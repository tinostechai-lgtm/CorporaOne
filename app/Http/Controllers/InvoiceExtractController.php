<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class InvoiceExtractController extends Controller
{
    public function index()
    {
        return view('invoice.extract');
    }

    public function process(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:pdf,jpg,jpeg,png|max:20480'
        ]);

        $file = $request->file('file');
        $path = $file->store('invoices', 'public');
        $fullPath = storage_path('app/public/' . $path);

        // Run Python script
        $pythonScript = base_path('invoice_extractor.py');
        $command = escapeshellcmd("python \"$pythonScript\" \"$fullPath\"");
        $output = shell_exec($command);

        if ($output === null) {
            return back()->withErrors(['error' => 'Failed to process the file. Please check Python installation and dependencies.']);
        }

        $invoices = json_decode($output, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors(['error' => 'Failed to parse extraction results.']);
        }

        if (isset($invoices['error'])) {
            return back()->withErrors(['error' => $invoices['error']]);
        }

        return view('invoice.results', compact('invoices', 'path'));
    }

    private function extractText($filePath, $extension)
    {
        $text = "";

        if (strtolower($extension) === 'pdf') {
            try {
                $parser = new PdfParser();
                $pdf = $parser->parseFile($filePath);
                $text = $pdf->getText();
            } catch (\Exception $e) {
                $text = "";
            }
        }

        if (empty(trim($text))) {
            try {
                $pdfToImageClass = '\Spatie\PdfToImage\Pdf';
                $image = strtolower($extension) === 'pdf'
                    ? InterventionImage::make($pdfToImageClass::create($filePath)->page(1))
                    : InterventionImage::make($filePath);

                $processed = $this->preprocessImage($image);

                $ocr = new TesseractOCR();
                $ocr->image($processed->encode('png')->getEncoded());
                $ocr->lang('eng');
                $ocr->oem(3);
                $ocr->psm(6);
                $text = $ocr->run();
            } catch (\Exception $e) {
                $text = "";
            }
        }

        return $text;
    }

    private function preprocessImage($image)
    {
        return $image
            ->greyscale()
            ->contrast(50)
            ->brightness(10)
            ->sharpen(8)
            ->resize(2000, null, function ($constraint) { $constraint->aspectRatio(); $constraint->upsize(); });
    }

    private function extractInvoiceFields($text)
    {
        // Same as before
        $clean = preg_replace("/\s{2,}/", " ", $text);
        $clean = preg_replace("/(?i)invo\s*ice|invoie|oice|invoic/", "Invoice", $clean);
        $lines = array_filter(array_map('trim', explode("\n", $clean)));

        $invoicePattern = '/\b([A-Za-z]{0,4}[A-Za-z0-9]*[\/\-]?\d{1,6}(?:[\/\-]\d{1,6}){0,2})\b/';
        $badPattern = '/(gst|kerala|total|state|amount|phone|code|eway|tax|vehicle|invoice)/i';

        $invoiceNo = "Not Found";

        foreach ($lines as $ln) {
            if (preg_match('/invoice|inv|bill/i', $ln)) {
                preg_match_all($invoicePattern, $ln, $matches);
                foreach ($matches[0] as $m) {
                    if (preg_match('/\d{3,}/', $m) && !preg_match($badPattern, $m) && !preg_match('/^[A-Za-z]+$/', $m)) {
                        $invoiceNo = $m;
                        break 2;
                    }
                }
            }
        }

        if ($invoiceNo === "Not Found") {
            foreach ($lines as $i => $ln) {
                if (preg_match('/invoice|inv|bill/i', $ln) && isset($lines[$i + 1])) {
                    preg_match_all($invoicePattern, $lines[$i + 1], $matches);
                    foreach ($matches[0] as $m) {
                        if (preg_match('/\d{3,}/', $m) && !preg_match($badPattern, $m) && !preg_match('/^[A-Za-z]+$/', $m)) {
                            $invoiceNo = $m;
                            break 2;
                        }
                    }
                }
            }
        }

        if ($invoiceNo === "Not Found") {
            preg_match_all($invoicePattern, $clean, $matches);
            foreach ($matches[0] as $m) {
                if (preg_match('/\d{3,}/', $m) && !preg_match($badPattern, $m) && !preg_match('/^[A-Za-z]+$/', $m)) {
                    $invoiceNo = $m;
                    break;
                }
            }
        }

        $datePattern = '/\b(\d{1,2}[\/\-\.][A-Za-z0-9]{1,3}[\/\-\.]\d{2,4}|\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{2,4})\b/';
        $date = "Not Found";
        foreach ($lines as $ln) {
            if (preg_match($datePattern, $ln, $m)) {
                $date = $m[1];
                break;
            }
        }
        if ($date === "Not Found") {
            preg_match($datePattern, $clean, $m);
            $date = $m[1] ?? "Not Found";
        }

        return [$invoiceNo, $date];
    }

    private function extractItems($text)
    {
        $lines = array_filter(array_map('trim', explode("\n", $text)));

        $items = [];
        $inTable = false;
        $currentItem = null;
        $fieldIndex = 0;
        $amountPattern = '/^[\d,]+\.\d{2}$/';  // Matches amounts like 3,159.00 or 816.00

        $discPattern = '/^[\d,]+(?:\.\d{2})?$/'; // For disc amt like 1,701 or 384

        $perPattern = '/^(Pcs|Nos|set)$/i'; // Unit

        $ratePattern = '/^[\d,]+\.\d{2}$/';

        $quantityPattern = '/^[\d.]+ (Pcs|Nos|set)$/i';

        $gstPattern = '/^18 %$/';

        $hsnPattern = '/^\d{8}$/';

        foreach ($lines as $line) {
            if (preg_match('/^\d+\s/', $line) && preg_match('/[A-Za-z]/', $line)) { // Likely start of item
                if ($currentItem) {
                    $items[] = $currentItem;
                }
                $parts = preg_split('/\s{2,}/', $line); // Split by multiple spaces if OCR merges
                $sl = array_shift($parts);
                $desc = implode(' ', $parts);
                $currentItem = [
                    'sl_no' => $sl,
                    'description' => trim($desc),
                    'hsn_sac' => '',
                    'gst_rate' => '',
                    'quantity' => '',
                    'unit' => '',
                    'unit_rate' => '',
                    'total_discount' => '',
                    'line_amount' => '',
                ];
                $inTable = true;
                $fieldIndex = 0;
                continue;
            } elseif ($inTable && $currentItem) {
                if ($fieldIndex == 0) {
                    if (preg_match($amountPattern, $line)) {
                        $currentItem['line_amount'] = str_replace(',', '', $line);
                        $fieldIndex = 1;
                    } elseif (preg_match('/[A-Za-z]/', $line)) {
                        $currentItem['description'] .= ' ' . $line;
                    }
                } elseif ($fieldIndex == 1) {
                    if (preg_match($discPattern, $line)) {
                        $currentItem['total_discount'] = str_replace(',', '', $line);
                        $fieldIndex = 2;
                    }
                } elseif ($fieldIndex == 2) {
                    if (preg_match($perPattern, $line)) {
                        $currentItem['unit'] = strtolower($line);
                        $fieldIndex = 3;
                    }
                } elseif ($fieldIndex == 3) {
                    if (preg_match($ratePattern, $line)) {
                        $currentItem['unit_rate'] = str_replace(',', '', $line);
                        $fieldIndex = 4;
                    }
                } elseif ($fieldIndex == 4) {
                    if (preg_match($quantityPattern, $line, $qmatch)) {
                        $currentItem['quantity'] = $qmatch[1];
                        $fieldIndex = 5;
                    }
                } elseif ($fieldIndex == 5) {
                    if (preg_match($gstPattern, $line)) {
                        $currentItem['gst_rate'] = '18%';
                        $fieldIndex = 6;
                    }
                } elseif ($fieldIndex == 6) {
                    if (preg_match($hsnPattern, $line)) {
                        $currentItem['hsn_sac'] = $line;
                        $items[] = $currentItem;
                        $currentItem = null;
                        $fieldIndex = 0;
                    }
                }
            }

            if (preg_match('/(total|cgst|sgst|round off)/i', $line)) {
                if ($currentItem) {
                    $items[] = $currentItem;
                }
                $inTable = false;
            }
        }

        if ($currentItem) {
            $items[] = $currentItem;
        }

        return $items;
    }

    private function autoDetectGst($text)
    {
        $out = ["gst_percent" => null, "gst_amount" => null];

        if (preg_match('/(\b\d{1,2}(?:\.\d+)?)\s*%[\s]*(?:gst|tax)?/i', $text, $m)) {
            $out["gst_percent"] = $m[1] . "%";
        }

        if (preg_match('/(?:gst\s*amount|tax\s*amount|cgst|sgst|igst|gst)[^\d₹\-—]*([₹]?\s*[\d,]+(?:\.\d{1,2})?)/i', $text, $m)) {
            $out["gst_amount"] = preg_replace('/[^\d.]/', '', $m[1]);
        }

        return $out;
    }

    public function saveAndDownload(Request $request)
    {
        $data = $request->all();
        $invoicesData = json_decode($data['invoices_data'], true);

        $rows = [];

        foreach ($invoicesData as $inv) {
            foreach ($inv['item_list'] as $item) {
                $rows[] = [
                    "Invoice Number" => $inv['invoice_no'],
                    "Date" => $inv['date'],
                    "Page" => $item['Page'] ?? '',
                    "Page Start" => $inv['page_start'],
                    "Page End" => $inv['page_end'],
                    "Seller Name" => $inv['seller']['name'],
                    "Seller GSTIN" => $inv['seller']['gstin'],
                    "Buyer Name" => $inv['buyer']['name'],
                    "Buyer GSTIN" => $inv['buyer']['gstin'],
                    "Serial Number" => $item['Serial Number'] ?? '',
                    "Description" => $item['Description'],
                    "Amount" => $item['Amount'],
                    "GST %" => $item['GST %'] ?? 0,
                    "GST Amount" => $item['GST Amount'] ?? 0,
                    "Total Amount" => $item['Total Amount'] ?? 0,
                    "Locked" => $item['__locked__'] ?? false,
                    "Reconcile Note" => $item['__reconcile_note__'] ?? ''
                ];
            }

            $rows[] = [
                "Invoice Number" => $inv['invoice_no'],
                "Date" => $inv['date'],
                "Description" => "FINAL COMPUTED TOTAL",
                "Total Amount" => $inv['final_invoice_total']
            ];
        }

        $masterPath = storage_path('app/public/extract_result.xlsx');

        if (file_exists($masterPath)) {
            $existing = Excel::toArray([], $masterPath)[0];
            $header = array_shift($existing);
            $finalData = array_merge([$header], $existing, $rows);
        } else {
            $finalData = array_merge([
                ["Invoice Number", "Date", "Page", "Page Start", "Page End", "Seller Name", "Seller GSTIN", "Buyer Name", "Buyer GSTIN", "Serial Number", "Description", "Amount", "GST %", "GST Amount", "Total Amount", "Locked", "Reconcile Note"]
            ], $rows);
        }

        return Excel::download(new class($finalData) implements \Maatwebsite\Excel\Concerns\FromArray {
            protected $data;
            public function __construct($data) { $this->data = $data; }
            public function array(): array { return $this->data; }
        }, 'extract_result.xlsx');
    }
}