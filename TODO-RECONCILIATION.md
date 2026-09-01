# Bank Statement vs Ledger Reconciliation - Implementation Plan

## Current Status
✅ Ledger loading fixed (date-range filtering works)
✅ Bank statement upload & AI extraction working  
✅ Basic compare view exists

## Implementation Steps

### Step 1: ✅ Routes Added
**File:** `routes/web.php`
- Added `compare/{submission_id}` (single param)
- Added `compare/{ledger_id}/{submission_id}` (double param) 
- Prefixed routes preserved
- Now supports both formats!

### Step 2: [PENDING] Update Controller
**File:** `app/Http/Controllers/BankReconciliationController.php`
- Replace `compare()` with improved version:
  * Handle both route formats (`$param1/$param2` + request ledger_id)
  * Fix model: `ChartOfAccount::find()` only  
  * Fix transactions: `$bankStatement->transactions` (relation)
  * Add validation & creatorId filters
  * Improve fuzzy matching (amount ±1%, date ±3 days, description)
  * Default dates to statement period

### Step 3: [PENDING] Test Routes & Functionality
```
php artisan route:clear && php artisan cache:clear

Test 1: /bank-reconciliation/compare/5?ledger_id=1
Test 2: /bank-reconciliation/compare/1/5  
Test 3: Date filters ?start_date=2024-01-01&end_date=2024-01-31
Test 4: Empty ledger_id → redirect with message
```

### Step 4: [COMPLETED LATER] View Improvements
**File:** `resources/views/bank-reconciliation/compare.blade.php`
- Add fuzzy match confidence scores
- Add manual match/unmatch buttons
- Export matched/unmatched to Excel

### Step 5: [COMPLETED LATER] PDF Report
- Add reconciliation report PDF button
- Summary: match rate, differences, outliers

## Dependencies ✅
- `JournalItem` model (ledger source)
- `ChartOfAccount` model ✓
- `BankStatement->transactions` relation ✓  
- `Utility::getAccountData()` (helper)

## Completion Criteria
- [ ] Both route formats work
- [ ] Proper error redirects
- [ ] 85%+ fuzzy matching accuracy
- [ ] View renders correctly
- [ ] Date filtering works

**Next:** Step 1 - Update routes
