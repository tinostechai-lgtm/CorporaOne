import re
from dataclasses import dataclass
import json
import sys
import os
from pathlib import Path


@dataclass
class FieldResult:
    value: str | None
    confidence: float


def main():
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No PDF file provided"}), flush=True)
        sys.exit(1)

    pdf_path = sys.argv[1]
    
    try:
        # Read PDF text (pdfplumber/pdf2image need to be installed)
        text = extract_text(pdf_path)
        
        result = extract_structured_data(text)
        print(json.dumps(result, indent=2), flush=True)
        
    except Exception as e:
        print(json.dumps({"error": str(e)}), flush=True)
        sys.exit(1)


DATE_RE = re.compile(r"\b(?:\d{4}[-/]\d{1,2}[-/]\d{1,2}|\d{1,2}[-/]\d{1,2}[-/]\d{2,4})\b")
AMOUNT_RE = re.compile(r"[-+]?\d[\d,]*\.?\d{0,2}")
IFSC_RE = re.compile(r"\b([A-Z]{4}0[A-Z0-9]{6})\b", re.IGNORECASE)
ACCOUNT_FALLBACK_RE = re.compile(r"\b\d{9,18}\b")
REF_CODE_RE = re.compile(r"#?[A-Z]{2,}\d{2,}", re.IGNORECASE)


def _normalize_line(value: str) -> str:
    return " ".join(value.replace("|", " ").replace("_", " ").split()).strip(" :-")


def _extract_by_labels(lines: list[str], labels: list[str], confidence: float) -> FieldResult:
    escaped = [re.escape(label) for label in labels]
    pattern = re.compile(
        rf"^\s*(?:{'|'.join(escaped)})\s*[:\-]?\s*(.*)$",
        re.IGNORECASE,
    )

    for index, line in enumerate(lines):
        match = pattern.match(line)
        if not match:
            continue

        value = _normalize_line(match.group(1))
        if value:
            return FieldResult(value=value, confidence=confidence)

        if index + 1 < len(lines):
            next_line = _normalize_line(lines[index + 1])
            if next_line:
                return FieldResult(value=next_line, confidence=max(0.5, confidence - 0.2))

    return FieldResult(value=None, confidence=0.0)


def _extract_ifsc(text: str, lines: list[str]) -> FieldResult:
    direct = IFSC_RE.search(text)
    if direct:
        return FieldResult(value=direct.group(1).upper(), confidence=0.9)

    from_label = _extract_by_labels(lines, ["ifsc", "ifsc code", "ifs code"], 0.8)
    if from_label.value:
        compact = re.sub(r"\s+", "", from_label.value).upper()
        maybe = IFSC_RE.search(compact)
        if maybe:
            return FieldResult(value=maybe.group(1).upper(), confidence=0.75)
    return FieldResult(value=None, confidence=0.0)


def _extract_account_number(text: str, lines: list[str]) -> FieldResult:
    labeled = _extract_by_labels(
        lines,
        ["account number", "account no", "a/c no", "ac no", "acc no"],
        0.85,
    )
    if labeled.value:
        digits = re.sub(r"\D", "", labeled.value)
        if 9 <= len(digits) <= 18:
            return FieldResult(value=digits, confidence=0.85)

    matches = ACCOUNT_FALLBACK_RE.findall(text)
    if matches:
        best = max(matches, key=len)
        return FieldResult(value=best, confidence=0.55)

    return FieldResult(value=None, confidence=0.0)


def _extract_bank_name(lines: list[str]) -> FieldResult:
    labeled = _extract_by_labels(lines, ["bank name", "bank"], 0.8)
    if labeled.value:
        return labeled

    for line in lines:
        cleaned = _normalize_line(line)
        if cleaned and "bank" in cleaned.lower() and len(cleaned.split()) <= 8:
            return FieldResult(value=cleaned, confidence=0.55)
    return FieldResult(value=None, confidence=0.0)


def _normalize_amount(value: str) -> str | None:
    if value == "-":
        return None
    normalized = value.replace(",", "").strip()
    if normalized in {"", "+", "-"}:
        return None
    return normalized


def _extract_transactions(lines: list[str]) -> list[dict]:
    transactions: list[dict] = []
    for line in lines:
        stripped = _normalize_line(line)
        if not stripped:
            continue

        date_match = DATE_RE.search(stripped)
        if not date_match:
            continue

        date_value = date_match.group(0)
        prefix = stripped[: date_match.start()].strip()
        suffix = stripped[date_match.end() :].strip()

        amounts = AMOUNT_RE.findall(suffix)
        if len(amounts) < 3:
            continue

        serial_match = re.match(r"^(\d{1,4})[\).\-\s]+", prefix)
        serial_number = int(serial_match.group(1)) if serial_match else (len(transactions) + 1)

        description = prefix
        if serial_match:
            description = prefix[serial_match.end() :].strip()

        ref_match = REF_CODE_RE.search(description)
        transaction_type = ref_match.group(0) if ref_match else description.split()[-1] if description.split() else "Transaction"
        purpose_text = description

        descriptor = description
        if ref_match:
            descriptor = _normalize_line(description.replace(ref_match.group(0), "").strip())

        parts = descriptor.split()
        if len(parts) >= 2:
            account_name = " ".join(parts[:-1])
            person_name = parts[-1]
        else:
            account_name = descriptor
            person_name = ""

        debit = _normalize_amount(amounts[0])
        credit = _normalize_amount(amounts[1])
        balance = _normalize_amount(amounts[2])

        transactions.append(
            {
                "serial_number": serial_number,
                "account_name": account_name,
                "name": person_name,
                "date": date_value,
                "type": transaction_type,
                "purpose": purpose_text,
                "debit": debit,
                "credit": credit,
                "balance": balance,
            }
        )

    return transactions


def extract_structured_data(text: str) -> dict:
    lines = [line.strip() for line in text.splitlines() if line.strip()]

    account_name = _extract_by_labels(lines, ["account name", "a/c name", "name"], 0.8)
    account_number = _extract_account_number(text, lines)
    ifsc = _extract_ifsc(text, lines)
    bank_name = _extract_bank_name(lines)
    branch = _extract_by_labels(lines, ["branch", "branch name"], 0.8)
    transactions = _extract_transactions(lines)

    confidence = {
        "account_name": account_name.confidence,
        "account_number": account_number.confidence,
        "ifsc_code": ifsc.confidence,
        "bank_name": bank_name.confidence,
        "branch": branch.confidence,
        "transactions": min(1.0, 0.5 + (0.1 * len(transactions))) if transactions else 0.0,
    }

    return {
        "account_name": account_name.value,
        "account_number": account_number.value,
        "ifsc_code": ifsc.value,
        "bank_name": bank_name.value,
        "branch": branch.value,
        "transactions": transactions,
        "confidence": confidence,
    }


if __name__ == "__main__":
    main()

