# Bank Reconciliation - Ledger Report Pattern Implementation

## Information Gathered:
**Ledger Report (`/ledger-report`) Pattern Analysis:**
```
✅ Route: GET /report/ledger?account=X&start_date=Y&end_date=Z  
✅ Controller: ReportController::ledgerSummary()
✅ Data: Utility::getAccountData() → $accountData[] (account+transactions+totals)
✅ View: ledger_summary.blade.php (@foreach $accountData)
✅ Features: Filter form → Submit → Server-rendered table + PDF export
✅ Running balances + account totals + grand totals
```

**Current Bank Reconciliation Status:**
```
✅ Upload bank statements (BankStatementController)
✅ Extract transactions (AI service) 
✅ Compare logic (BankReconciliationController::compareTransactions)
❌ Transaction preview not loading (empty TransactionLines?)
```

## Plan:
**Step 1:** Add `/bank-reconciliation` GET filter form (copy ledger_report form)
**Step 2:** Route `/bank-reconciliation/report` → BankReconciliationController::ledgerReport()
**Step 3:** Implement `ledgerReport()` → Copy ReportController::ledgerSummary logic
**Step 4:** Use shared `ledger_summary.blade.php` 
**Step 5:** Add "Compare" button → Side-by-side bank/ledger
**Step 6:** AJAX preview table while filtering

**Files to Edit:**
1. `resources/views/bank-reconciliation/index.blade.php` - Add GET form + Submit → `/bank-reconciliation/report`
2. `app/Http/Controllers/BankReconciliationController.php` - Add `ledgerReport()` method
3. `routes/web.php` - Add route `bank-reconciliation/report`
4. `resources/views/bank-reconciliation/ledger-preview.blade.php` - New AJAX table

**Dependent Files:**
- `app/Models/Utility.php` - `getAccountData()` (PROVEN working)
- `resources/views/report/ledger_summary.blade.php` (REUSE)
- `TransactionLines` table (data source)

## Follow-up Steps:
1. `php artisan route:clear && php artisan view:clear`
2. Test: `/bank-reconciliation` → Filter → See transactions  
3. Test AJAX preview: Select account → Live table updates
4. Compare with uploaded bank statement
5. Verify PDF export works

**Ready for Implementation?** Confirm to proceed with file edits.
