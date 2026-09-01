# CorporaOne - Accounting Setup Full Working Flow Documentation

---

## Table of Contents
1. [System Overview](#system-overview)
2. [Accounting Module Architecture](#accounting-module-architecture)
3. [Initial Setup Flow](#initial-setup-flow)
4. [Chart of Accounts](#chart-of-accounts)
5. [Bank Account Management](#bank-account-management)
6. [Invoice Management (Accounts Receivable)](#invoice-management-accounts-receivable)
7. [Bill Management (Accounts Payable)](#bill-management-accounts-payable)
8. [Revenue & Payments](#revenue--payments)
9. [Journal Entries](#journal-entries)
10. [Financial Reports](#financial-reports)
11. [Transaction Flow Diagrams](#transaction-flow-diagrams)
12. [Integration with Other Modules](#integration-with-other-modules)
13. [Payment Gateway Integration](#payment-gateway-integration)
14. [Common Workflows](#common-workflows)

---

## 1. System Overview

The **Accounting Module** in CorporaOne is a comprehensive double-entry bookkeeping system built on Laravel. It handles all financial transactions, from recording income and expenses to generating financial statements.

### Key Features:
- Double-entry bookkeeping
- Multi-currency support (via payment gateways)
- Chart of Accounts management
- Bank Account integration
- Invoice & Bill management
- Revenue & Payment tracking
- Journal Entry system
- Comprehensive financial reporting
- 30+ Payment Gateway integrations

---

## 2. Accounting Module Architecture

### Database Models Hierarchy:

```
ChartOfAccountType (1)
    ↓
ChartOfAccountSubType (Many)
    ↓
ChartOfAccount (Many)
    ↓
ChartOfAccountParent (Many)
    ↓
TransactionLines (Many) - Records all debit/credit transactions
```

### Core Models:

| Model | Purpose |
|-------|---------|
| `ChartOfAccountType` | Main account categories (Assets, Liabilities, Equity, Income, Expenses) |
| `ChartOfAccountSubType` | Sub-categories under each type |
| `ChartOfAccount` | Individual ledger accounts |
| `ChartOfAccountParent` | Parent accounts for hierarchical structure |
| `BankAccount` | Cash and bank accounts |
| `Invoice` | Customer invoices (Accounts Receivable) |
| `InvoicePayment` | Payments received against invoices |
| `Bill` | Vendor bills (Accounts Payable) |
| `BillPayment` | Payments made against bills |
| `Revenue` | Direct income recording |
| `Payment` | Direct expense recording |
| `JournalEntry` | Manual journal entries |
| `JournalItem` | Individual debit/credit lines in journal |
| `Transaction` | General transaction logging |
| `TransactionLines` | Detailed transaction line items |

---

## 3. Initial Setup Flow

When a new company/user is created in CorporaOne, the accounting setup is automatically initialized via the `UserController` and `Utility` class.

### Automatic Setup Process:

```php
// In UserController - when creating a new user/company
$user->userDefaultBankAccount($user->id);
Utility::chartOfAccountTypeData($user->id);
Utility::chartOfAccountData1($user->id);
```

### Default Chart of Account Types Created:

```php
// From Utility.php - $chartOfAccountType
[
    'assets' => 'Assets',
    'liabilities' => 'Liabilities', 
    'equity' => 'Equity',
    'income' => 'Income',
    'expenses' => 'Expenses',
]
```

### Default Sub-Types:

```php
// From Utility.php - $chartOfAccountSubType
[
    'assets' => ['Current Assets', 'Fixed Assets', 'Other Assets'],
    'liabilities' => ['Current Liabilities', 'Long Term Liabilities'],
    'equity' => ['Owner Equity', 'Retained Earnings'],
    'income' => ['Operating Income', 'Non-Operating Income'],
    'expenses' => ['Operating Expenses', 'Non-Operating Expenses']
]
```

### Default Chart of Accounts:

The system creates these default accounts:

| Code | Account Name | Type |
|------|--------------|------|
| 1060 | Checking Account | Assets |
| 1200 | Account Receivables | Assets |
| 1205 | Allowance for doubtful accounts | Assets |
| 1550 | Goods Received Clearing account | Assets |
| 2100 | Account Payable | Liabilities |
| 5610 | Accounting Fees | Expenses |

---

## 4. Chart of Accounts

### Purpose:
The Chart of Accounts (COA) is the foundation of the accounting system. It provides the framework for categorizing all financial transactions.

### Access:
```
Route: /chart-of-account
Controller: ChartOfAccountController
Permission: manage chart of account
```

### Account Types Hierarchy:

```
1. Assets
   ├── Current Assets
   │   ├── Cash & Bank
   │   ├── Accounts Receivable
   │   ├── Inventory
   │   └── Prepaid Expenses
   ├── Fixed Assets
   │   ├── Equipment
   │   ├── Vehicles
   │   └── Furniture
   └── Other Assets

2. Liabilities
   ├── Current Liabilities
   │   ├── Accounts Payable
   │   ├── Short-term Loans
   │   └── Accrued Expenses
   └── Long Term Liabilities
       └── Long-term Loans

3. Equity
   ├── Owner Capital
   ├── Retained Earnings
   └── Current Year Earnings

4. Income
   ├── Sales Revenue
   ├── Service Revenue
   └── Other Income

5. Expenses
   ├── Cost of Goods Sold
   ├── Operating Expenses
   └── Non-operating Expenses
```

### Creating a New Account:

1. Navigate to **Accounting → Chart of Accounts**
2. Click **Create New Account**
3. Fill in:
   - **Account Name**: e.g., "Office Supplies Expense"
   - **Account Code**: e.g., "6200"
   - **Type**: Select from dropdown (Assets, Liabilities, etc.)
   - **Sub Type**: Select sub-category
   - **Parent Account**: Optional - for hierarchical accounts
   - **Description**: Optional notes
   - **Is Enabled**: Toggle for active/inactive
4. Click **Save**

### Route Definitions:
```php
// routes/web.php
Route::post('chart-of-account/subtype', [ChartOfAccountController::class, 'getSubType'])->name('charofAccount.subType');
Route::resource('chart-of-account', ChartOfAccountController::class);
```

---

## 5. Bank Account Management

### Purpose:
Manage cash and bank accounts, track balances, and link to Chart of Accounts.

### Access:
```
Route: /bank-account
Controller: BankAccountController
Permission: create bank account
```

### Creating a Bank Account:

1. Navigate to **Accounting → Bank Accounts**
2. Click **Create New Account**
3. Fill in:
   - **Account Holder Name**: e.g., "Business Checking"
   - **Bank Name**: e.g., "First National Bank"
   - **Account Number**: Account number
   - **Chart of Account**: Select the linked COA account
   - **Opening Balance**: Initial balance
   - **Contact Number**: Optional
   - **Bank Address**: Optional
4. Click **Save**

### Bank Account Model:
```php
// From BankAccount.php
protected $fillable = [
    'holder_name',
    'bank_name', 
    'account_number',
    'chart_account_id',      // Links to Chart of Account
    'opening_balance',
    'contact_number',
    'bank_address',
    'created_by',
];
```

### Opening Balance Transaction:
When a bank account is created with an opening balance, the system automatically creates a transaction line:

```php
// From BankAccountController.php - store() method
$data = [
    'account_id' => $account->chart_account_id,
    'transaction_type' => 'Credit',
    'transaction_amount' => $account->opening_balance,
    'reference' => 'Bank Account',
    'reference_id' => $account->id,
    'date' => date('Y-m-d'),
];
Utility::addTransactionLines($data, 'create');
```

### Route Definitions:
```php
Route::resource('bank-account', BankAccountController::class);
Route::resource('bank-transfer', BankTransferController::class);
```

---

## 6. Invoice Management (Accounts Receivable)

### Purpose:
Create and manage customer invoices, track payments, and maintain accounts receivable.

### Invoice Status Flow:
```
Draft → Sent → Unpaid → Partialy Paid → Paid
```

### Creating an Invoice:

1. Navigate to **Accounting → Invoices**
2. Click **Create Invoice**
3. Fill in:
   - **Customer**: Select from customer list
   - **Invoice Date**: Issue date
   - **Due Date**: Payment due date
   - **Category**: Income category
   - **Products/Services**: Add line items
   - **Tax**: Apply tax rates
   - **Discount**: Apply discounts
   - **Notes**: Additional notes
4. Click **Save as Draft** or **Send**

### Invoice Status Definitions:
| Status | Description |
|--------|-------------|
| Draft | Not yet sent to customer |
| Sent | Invoice sent to customer |
| Unpaid | Past due date, no payment |
| Partially Paid | Partial payment received |
| Paid | Full payment received |

### Invoice Model:
```php
// From Invoice.php
protected $fillable = [
    'invoice_id',
    'customer_id',
    'issue_date',
    'due_date',
    'ref_number',
    'status',           // 0=Draft, 1=Sent, 2=Unpaid, 3=Partial, 4=Paid
    'category_id',
    'created_by',
];

// Key relationships
public function items()        // InvoiceProduct
public function payments()     // InvoicePayment  
public function customer()     // Customer
public function tax()          // Tax
```

### Recording Invoice Payments:

1. Open the invoice
2. Click **Add Payment**
3. Select:
   - **Payment Date**: Date received
   - **Account**: Bank account receiving payment
   - **Amount**: Payment amount
   - **Payment Method**: Cash, Bank Transfer, etc.
   - **Reference**: Transaction reference
4. Click **Save Payment**

### Payment Gateway Integration:
Invoices can be paid through 30+ payment gateways:
- PayPal, Stripe, Paystack
- Razorpay, Paytm
- Flutterwave, Mercado Pago
- And many more...

### Route Definitions:
```php
Route::get('invoice/{id}/payment', [InvoiceController::class, 'payment'])->name('invoice.payments');
Route::post('invoice/{id}/payment', [InvoiceController::class, 'createPayment'])->name('invoice.payment');
Route::resource('invoice', InvoiceController::class);
```

---

## 7. Bill Management (Accounts Payable)

### Purpose:
Create and manage vendor bills, track payments due, and maintain accounts payable.

### Bill Status Flow:
```
Draft → Sent → Unpaid → Partialy Paid → Paid
```

### Creating a Bill:

1. Navigate to **Accounting → Bills**
2. Click **Create Bill**
3. Fill in:
   - **Vendor**: Select from vendor list
   - **Bill Date**: Issue date
   - **Due Date**: Payment due date
   - **Category**: Expense category
   - **Products/Services**: Add line items
   - **Tax**: Apply tax rates
   - **Discount**: Apply discounts
4. Click **Save**

### Bill Model:
```php
// From Bill.php
protected $fillable = [
    'vender_id',
    'currency',
    'bill_date',
    'due_date',
    'bill_id',
    'order_number',
    'category_id',
    'created_by',
];

// Key relationships
public function items()        // BillProduct
public function accounts()     // BillAccount (for expense accounts)
public function payments()     // BillPayment
public function vender()       // Vendor
```

### Recording Bill Payments:

1. Open the bill
2. Click **Add Payment**
3. Select payment details
4. Click **Save Payment**

### Route Definitions:
```php
Route::get('bill/{id}/payments', [BillController::class, 'payment'])->name('bill.payments');
Route::post('bill/{id}/payment', [BillController::class, 'createPayment'])->name('bill.payment');
Route::resource('bill', BillController::class);
```

---

## 8. Revenue & Payments

### Purpose:
Record direct income and expense transactions outside of invoices/bills.

### Revenue (Direct Income):

Use for: Cash sales, interest income, other direct income

1. Navigate **Accounting → Revenue**
2. Click **Add Revenue**
3. Fill in:
   - **Date**: Transaction date
   - **Amount**: Income amount
   - **Account**: Bank account (or cash)
   - **Customer**: Optional - related customer
   - **Category**: Income category
   - **Payment Method**: How received
   - **Reference**: Transaction reference
   - **Description**: Notes
4. Click **Save**

### Revenue Model:
```php
// From Revenue.php
protected $fillable = [
    'date',
    'amount',
    'account_id',        // Bank account
    'customer_id',
    'category_id',
    'recurring',
    'payment_method',
    'reference',
    'description',
    'created_by',
];
```

### Payment (Direct Expense):

Use for: Cash purchases, utilities, rent, other direct expenses

1. Navigate **Accounting → Payments**
2. Click **Add Payment**
3. Fill in:
   - **Date**: Transaction date
   - **Amount**: Expense amount
   - **Account**: Bank account paid from
   - **Vendor**: Optional - related vendor
   - **Category**: Expense category
   - **Payment Method**: How paid
   - **Reference**: Transaction reference
   - **Description**: Notes
4. Click **Save**

### Payment Model:
```php
// From Payment.php
protected $fillable = [
    'date',
    'amount',
    'account_id',            // Bank account
    'chart_account_id',      // Expense account
    'vender_id',
    'description',
    'category_id',
    'payment_method',
    'reference',
    'created_by',
];
```

### Route Definitions:
```php
Route::resource('revenue', RevenueController::class);
Route::resource('payment', PaymentController::class);
```

---

## 9. Journal Entries

### Purpose:
Record manual journal entries for adjustments, depreciation, accruals, and other accounting transactions that don't fit other categories.

### Double-Entry System:
Every journal entry must have balanced debits and credits.

### Creating a Journal Entry:

1. Navigate **Accounting → Journal Entries**
2. Click **Create Journal Entry**
3. Fill in:
   - **Date**: Transaction date
   - **Reference**: Journal reference number
   - **Description**: Transaction description
4. Add multiple lines:
   - **Account**: Select account
   - **Debit**: Amount (or leave blank)
   - **Credit**: Amount (or leave blank)
5. Ensure total debits = total credits
6. Click **Save**

### Journal Entry Model:
```php
// From JournalEntry.php
protected $fillable = [
    'date',
    'reference',
    'description',
    'journal_id',
    'created_by',
];

// Key relationships
public function accounts()  // JournalItem (many)
public function totalCredit()
public function totalDebit()
```

### Journal Item Model:
```php
// From JournalItem.php
protected $fillable = [
    'journal',
    'account',
    'debit',
    'credit',
    'description',
    'created_by',
];
```

### Route Definitions:
```php
Route::resource('journal-entry', JournalEntryController::class);
```

---

## 10. Financial Reports

### Available Reports:

| Report | Route | Description |
|--------|-------|-------------|
| Income Summary | `/report/income-summary` | Monthly/yearly income breakdown |
| Expense Summary | `/report/expense-summary` | Monthly/yearly expense breakdown |
| Income vs Expense | `/report/income-vs-expense-summary` | Compare income and expenses |
| Tax Summary | `/report/tax-summary` | Tax collected/paid |
| Invoice Summary | `/report/invoice-summary` | Invoice status overview |
| Bill Summary | `/report/bill-summary` | Bill status overview |
| Balance Sheet | `/balance-sheet-report` | Assets, Liabilities, Equity |
| Profit & Loss | `/profit-loss-report` | Income statement |
| Trial Balance | `/trial-balance-report` | All account balances |
| Ledger Report | `/ledger-report` | Individual account transactions |
| Account Statement | `/report/account-statement-report` | Bank account transactions |

### Balance Sheet Report:
```php
// From ReportController.php - balanceSheet()
$types = ChartOfAccountType::where('created_by', \Auth::user()->creatorId())
    ->whereIn('name', ['Assets', 'Liabilities', 'Equity'])->get();
// Groups by type → sub_type → parent_account → accounts
```

### Profit & Loss Report:
```php
// From ReportController.php - profitLoss()
$types = ChartOfAccountType::where('created_by', \Auth::user()->creatorId())
    ->whereIn('name', ['Income', 'Expenses', 'Costs of Goods Sold'])->get();
```

### Trial Balance:
```php
// From ReportController.php - trialBalanceSummary()
$types = ChartOfAccountType::where('created_by', \Auth::user()->creatorId())->get();
// Lists all accounts with debit/credit balances
```

### Route Definitions:
```php
Route::get('report/income-summary', [ReportController::class, 'incomeSummary'])->name('report.income.summary');
Route::get('report/expense-summary', [ReportController::class, 'expenseSummary'])->name('report.expense.summary');
Route::get('balance-sheet-report/{view?}/{collapseview?}', [ReportController::class, 'balanceSheet'])->name('report.balance.sheet');
Route::get('profit-loss-report/{view?}/{collapseView?}', [ReportController::class, 'profitLoss'])->name('report.profit.loss');
Route::get('trial-balance-report/{view?}', [ReportController::class, 'trialBalanceSummary'])->name('trial.balance');
```

---

## 11. Transaction Flow Diagrams

### Invoice to Payment Flow:

```
1. Create Invoice
   ↓
2. Invoice saved with status "Draft"
   ↓
3. Send Invoice (status → "Sent")
   ↓
4. Customer makes payment
   ↓
5. Record Payment (partial or full)
   ↓
6a. If partial: status → "Partially Paid"
6b. If full: status → "Paid"
   ↓
7. Bank Account balance updated
   ↓
8. TransactionLines updated (debit/credit)
```

### Purchase Flow (Bill to Payment):

```
1. Create Bill from Vendor
   ↓
2. Bill saved with status "Draft"
   ↓
3. Send/Receive Bill (status → "Sent")
   ↓
4. Make Payment to Vendor
   ↓
5. Record Payment (partial or full)
   ↓
6a. If partial: status → "Partially Paid"
6b. If full: status → "Paid"
   ↓
7. Bank Account balance updated
   ↓
8. TransactionLines updated (debit/credit)
```

### Direct Revenue/Expense Flow:

```
Revenue (Income):
1. Create Revenue entry
   ↓
2. Select Bank Account
   ↓
3. Enter Amount & Category
   ↓
4. Save
   ↓
5. Bank Account credited
   ↓
6. Income Account credited

Payment (Expense):
1. Create Payment entry
   ↓
2. Select Bank Account
   ↓
3. Enter Amount & Category
   ↓
4. Save
   ↓
5. Bank Account debited
   ↓
6. Expense Account debited
```

### Journal Entry Flow:

```
1. Create Journal Entry
   ↓
2. Add multiple lines
   ↓
3. Each line: Debit OR Credit
   ↓
4. Validate: Total Debits = Total Credits
   ↓
5. Save Journal Entry
   ↓
6. TransactionLines created for each account
```

---

## 12. Integration with Other Modules

### HRM Integration:
- **Payroll**: Payslip generation creates journal entries
- **Employee Salaries**: Paid through Payment module
- **Expense Claims**: Recorded as Payments

### CRM Integration:
- **Invoices**: Linked to Customers
- **Deals**: Can generate invoices

### Project Management:
- **Project Expenses**: Recorded as Payments
- **Timesheet Billing**: Can generate invoices

### POS Integration:
- **POS Sales**: Creates Invoices automatically
- **POS Payments**: Updates bank accounts

### Inventory:
- **Product Costs**: Used in Bill/Invoice calculations
- **Stock Valuation**: Referenced in Balance Sheet

---

## 13. Payment Gateway Integration

### Supported Gateways (30+):
1. PayPal
2. Stripe
3. Paystack
4. Razorpay
5. Paytm
6. Flutterwave
7. Mercado Pago
8. Mollie
9. Skrill
10. Coingate
11. PayFast
12. Iyzipay
13. Sspay
14. Paytab
15. Benefit
16. Cashfree
17. Aamarpay
18. Paytr
19. YooKassa
20. Midtrans
21. Xendit
22. PaiementPro
23. Nepalste
24. Ozow
25. Fedapay
26. PayHere
27. Tap
28. Authorize.Net
29. Khalti
30. Easebuzz

### Invoice Payment Flow:

```
1. Customer views invoice
   ↓
2. Clicks "Pay Now"
   ↓
3. Selects payment gateway
   ↓
4. Redirected to payment page
   ↓
5. Completes payment
   ↓
6. Gateway webhook called
   ↓
7. System updates invoice status
   ↓
8. Payment recorded in InvoicePayment
   ↓
9. Bank Account balance updated
   ↓
10. Confirmation email sent
```

---

## 14. Common Workflows

### Scenario 1: Selling Products/Services on Credit

```
1. Create Customer (if not exists)
   └─ Route: /customer/create

2. Create Products/Services with price
   └─ Route: /productservice/create
   └─ Set Sale Chart of Account

3. Create Invoice
   └─ Route: /invoice/create
   └─ Add products, set due date

4. Send Invoice to customer
   └─ Click "Send" button
   └─ Status: Sent

5. Customer pays (partially or fully)
   └─ Route: /invoice/{id}/payment
   └─ Select payment method

6. Invoice marked Paid
   └─ Status: Paid
```

### Scenario 2: Purchasing from Vendor

```
1. Create Vendor (if not exists)
   └─ Route: /vender/create

2. Create Bill
   └─ Route: /bill/create
   └─ Add products/services
   └─ Set due date

3. Receive/Review Bill
   └─ Status: Sent

4. Make Payment
   └─ Route: /bill/{id}/payment
   └─ Select payment method

5. Bill marked Paid
   └─ Status: Paid
```

### Scenario 3: Recording Miscellaneous Income

```
1. Navigate to Revenue
   └─ Route: /revenue

2. Click "Add Revenue"

3. Fill details:
   - Date: Today
   - Amount: 500
   - Account: Business Checking
   - Category: Other Income
   - Description: Interest received

4. Save
   └─ Bank Account: +500 (Credit)
   └─ Interest Income: +500 (Credit)
```

### Scenario 4: Recording Miscellaneous Expense

```
1. Navigate to Payments
   └─ Route: /payment

2. Click "Add Payment"

3. Fill details:
   - Date: Today
   - Amount: 150
   - Account: Business Checking
   - Category: Office Supplies
   - Description: Printer paper

4. Save
   └─ Bank Account: -150 (Debit)
   └─ Office Supplies: -150 (Debit)
```

### Scenario 5: Month-End Journal Entry (Depreciation)

```
1. Navigate to Journal Entries
   └─ Route: /journal-entry

2. Create New Entry

3. Add lines:
   Line 1:
   - Account: Depreciation Expense
   - Debit: 500
   
   Line 2:
   - Account: Accumulated Depreciation
   - Credit: 500

4. Description: Monthly depreciation

5. Save
   └─ Both accounts updated
```

---

## 15. Purchase Module (Accounts Payable - Detailed)

### Overview:
The Purchase module handles all procurement activities - from creating purchase orders to recording payments to vendors. It integrates with inventory management to track stock levels.

### Purchase Status Flow:
```
Draft → Sent → Unpaid → Partially Paid → Paid
```

### Key Components:

#### 15.1 Purchase Order Management

**Route:** `/purchase`
**Controller:** `PurchaseController`
**Permission:** create purchase

**Database Models:**
- `Purchase` - Main purchase record
- `PurchaseProduct` - Line items (products purchased)
- `PurchasePayment` - Payment records

**Purchase Model:**
```php
// From Purchase.php
protected $fillable = [
    'purchase_id',
    'vender_id',
    'warehouse_id',
    'purchase_date',
    'purchase_number',
    'discount_apply',
    'category_id',
    'created_by',
];

public static $statues = [
    'Draft',        // 0 - Not yet sent
    'Sent',         // 1 - Sent to vendor
    'Unpaid',       // 2 - Payment due
    'Partially Paid', // 3 - Partial payment made
    'Paid',         // 4 - Fully paid
];
```

#### 15.2 Creating a Purchase

**Step-by-Step Process:**

1. **Navigate to Purchase Module**
   ```
   Route: /purchase/create
   ```

2. **Select Vendor**
   - Required field
   - Must be pre-created in Vendor module
   - Link: `/vender/create`

3. **Select Warehouse**
   - Required field
   - Determines where purchased items will be stored
   - Triggers inventory updates

4. **Set Purchase Date**
   - Date when purchase is made

5. **Select Category**
   - Expense category for accounting purposes
   - Links to Chart of Accounts

6. **Add Products/Services**
   - Select from product list
   - Enter quantity
   - Set unit price
   - Apply tax rates
   - Apply discounts

7. **Save Purchase**
   - Creates Purchase record
   - Creates PurchaseProduct records for each item
   - Updates inventory quantities
   - Creates stock reports

**Purchase Creation Flow (Code):**
```php
// From PurchaseController.php - store() method

// 1. Create Purchase Record
$purchase = new Purchase();
$purchase->purchase_id = $this->purchaseNumber();
$purchase->vender_id = $request->vender_id;
$purchase->warehouse_id = $request->warehouse_id;
$purchase->purchase_date = $request->purchase_date;
$purchase->category_id = $request->category_id;
$purchase->status = 0; // Draft
$purchase->save();

// 2. Add Products
for($i = 0; $i < count($products); $i++)
{
    $purchaseProduct = new PurchaseProduct();
    $purchaseProduct->purchase_id = $purchase->id;
    $purchaseProduct->product_id = $products[$i]['item'];
    $purchaseProduct->quantity = $products[$i]['quantity'];
    $purchaseProduct->price = $products[$i]['price'];
    $purchaseProduct->tax = $products[$i]['tax'];
    $purchaseProduct->discount = $products[$i]['discount'];
    $purchaseProduct->save();

    // 3. Update Inventory
    Utility::total_quantity('plus', $purchaseProduct->quantity, $purchaseProduct->product_id);
    
    // 4. Add Stock Report
    Utility::addProductStock($product_id, $quantity, 'purchase', $description, $purchase_id);
    
    // 5. Update Warehouse Stock
    Utility::addWarehouseStock($product_id, $quantity, $warehouse_id);
}
```

#### 15.3 Inventory Management Integration

**Automatic Updates on Purchase:**

1. **Product Quantity Update**
   ```php
   Utility::total_quantity('plus', $quantity, $product_id);
   // Increases product quantity in product_services table
   ```

2. **Stock Report Creation**
   ```php
   Utility::addProductStock($product_id, $quantity, 'purchase', $description, $purchase_id);
   // Creates audit trail of stock changes
   ```

3. **Warehouse Stock Update**
   ```php
   Utility::addWarehouseStock($product_id, $quantity, $warehouse_id);
   // Updates warehouse-specific inventory
   ```

#### 15.4 Sending Purchase to Vendor

**Route:** `/purchase/{id}/sent`
**Permission:** send purchase

**Process:**
```php
// From PurchaseController.php - sent() method

$purchase = Purchase::where('id', $id)->first();
$purchase->send_date = date('Y-m-d');
$purchase->status = 1; // Sent
$purchase->save();

// Update Vendor Balance (Accounts Payable)
$vender = Vender::find($purchase->vender_id);
Utility::userBalance('vendor', $vender->id, $purchase->getTotal(), 'credit');

// Send Email to Vendor
$purchase->url = route('purchase.pdf', $purchaseId);
Utility::sendEmailTemplate('vender_bill_sent', [$vender->email], $vendorArr);
```

#### 15.5 Recording Purchase Payments

**Route:** `/purchase/{id}/payment`
**Permission:** create payment purchase

**Payment Process:**
```php
// From PurchaseController.php - createPayment() method

// 1. Create Payment Record
$purchasePayment = new PurchasePayment();
$purchasePayment->purchase_id = $purchase_id;
$purchasePayment->date = $request->date;
$purchasePayment->amount = $request->amount;
$purchasePayment->account_id = $request->account_id;
$purchasePayment->payment_method = $request->payment_method;
$purchasePayment->reference = $request->reference;
$purchasePayment->save();

// 2. Update Purchase Status
$due = $purchase->getDue();
if($due <= 0) {
    $purchase->status = 4; // Paid
} else {
    $purchase->status = 3; // Partially Paid
}
$purchase->save();

// 3. Create Transaction Record
Transaction::addTransaction($purchasePayment);

// 4. Update Vendor Balance
Utility::userBalance('vendor', $purchase->vender_id, $request->amount, 'debit');

// 5. Update Bank Account Balance
Utility::bankAccountBalance($request->account_id, $request->amount, 'debit');
```

#### 15.6 Purchase Return Management

**Route:** `/purchase-return`
**Controller:** `PurchaseReturnController`

**Creating Purchase Return:**
```php
// From PurchaseReturnController.php

$purchaseReturn = new PurchaseReturn();
$purchaseReturn->supplier = $request->supplier;
$purchaseReturn->return_date = $request->return_date;
$purchaseReturn->description = $request->description;
$purchaseReturn->items = $request->items;
$purchaseReturn->total_amount = $request->total_amount;
$purchaseReturn->status = $request->status ?? 'pending';
$purchaseReturn->created_by = \Auth::user()->creatorId();
$purchaseReturn->save();
```

#### 15.7 Purchase Related Models

| Model | Purpose |
|-------|---------|
| `Purchase` | Main purchase document |
| `PurchaseProduct` | Line items with quantity, price, tax |
| `PurchasePayment` | Payment records against purchases |
| `Vender` | Vendor/supplier information |
| `Warehouse` | Storage location |
| `WarehouseProduct` | Warehouse-specific inventory |
| `StockReport` | Audit trail of stock movements |

#### 15.8 Complete Purchase Workflow

```
1. CREATE VENDOR (if not exists)
   └─ Route: /vender/create
   └─ Required: Name, email, phone, address

2. CREATE PRODUCTS
   └─ Route: /productservice/create
   └─ Set: Name, SKU, price, quantity, tax

3. CREATE PURCHASE
   └─ Route: /purchase/create
   └─ Select: Vendor, Warehouse, Category
   └─ Add: Products with quantities
   └─ Apply: Taxes, Discounts
   └─ Status: Draft (0)

4. SEND PURCHASE
   └─ Click "Send" button
   └─ Status: Sent (1)
   └─ Email sent to vendor
   └─ Vendor balance updated (Accounts Payable +)

5. RECEIVE GOODS
   └─ Inventory automatically updated
   └─ Product quantities increased
   └─ Warehouse stock updated
   └─ Stock reports created

6. MAKE PAYMENT
   └─ Route: /purchase/{id}/payment
   └─ Select: Bank account, amount, method
   └─ Status: Partially Paid (3) or Paid (4)

7. PAYMENT EFFECTS
   └─ Bank account debited
   └─ Vendor balance reduced
   └─ Transaction recorded

8. PURCHASE RETURN (optional)
   └─ Route: /purchase-return/create
   └─ Select: Supplier, products, quantities
   └─ Status: pending / approved / rejected
```

#### 15.9 Purchase Routes

```php
// From routes/web.php

// Purchase Management
Route::resource('purchase', PurchaseController::class);
Route::get('purchase/items', [PurchaseController::class, 'items'])->name('purchase.items');
Route::get('purchase/{id}/sent', [PurchaseController::class, 'sent'])->name('purchase.sent');
Route::get('purchase/{id}/resent', [PurchaseController::class, 'resent'])->name('purchase.resent');
Route::get('purchase/{id}/payments', [PurchaseController::class, 'payment'])->name('purchase.payments');
Route::post('purchase/{id}/payment', [PurchaseController::class, 'createPayment'])->name('purchase.payment');
Route::post('purchase/{id}/payment/{pid}/destroy', [PurchaseController::class, 'paymentDestroy'])->name('purchase.payment.destroy');
Route::post('purchase/product/destroy', [PurchaseController::class, 'productDestroy'])->name('purchase.product.destroy');
Route::post('purchase/vender', [PurchaseController::class, 'vender'])->name('purchase.vender');
Route::post('purchase/product', [PurchaseController::class, 'product'])->name('purchase.product');

// Purchase Return Management
Route::resource('purchase-return', PurchaseReturnController::class);
Route::post('purchase-return/product', [PurchaseReturnController::class, 'product'])->name('purchase-return.product');
Route::post('purchase-return/vender', [PurchaseReturnController::class, 'vender'])->name('purchase-return.vender');

// Purchase Reports
Route::get('reports-daily-purchase', [ReportController::class, 'purchaseDailyReport'])->name('report.daily.purchase');
Route::get('reports-monthly-purchase', [ReportController::class, 'purchaseMonthlyReport'])->name('report.monthly.purchase');
```

#### 15.10 Purchase Calculations

**From Purchase Model:**
```php
// Get subtotal (before tax and discount)
public function getSubTotal(): float
{
    return $this->items->sum(function($item) {
        return $item->price * $item->quantity;
    });
}

// Get total discount
public function getTotalDiscount(): float
{
    return $this->items->sum('discount');
}

// Get total tax
public function getTotalTax(): float
{
    $taxData = Utility::getTaxData();
    return $this->items->sum(function($item) use ($taxData) {
        $taxes = 0;
        $taxArr = explode(',', $item->tax);
        foreach ($taxArr as $tax) {
            $rate = $taxData[$tax]['rate'] ?? 0;
            $taxes += ($rate / 100) * ($item->price * $item->quantity);
        }
        return $taxes;
    });
}

// Get grand total
public function getTotal(): float
{
    return ($this->getSubTotal() - $this->getTotalDiscount()) + $this->getTotalTax();
}

// Get due amount
public function getDue(): float
{
    return $this->getTotal() - $this->payments->sum('amount');
}
```

#### 15.11 Deleting Purchases

**Process:**
```php
// From PurchaseController.php - destroy() method

// 1. Delete all purchase products
// 2. Reverse inventory updates:
//    - Reduce warehouse quantities
//    - Reduce product quantities
//    - Delete stock reports
// 3. Delete all purchase payments
// 4. Delete purchase record
```

---

## 16. API Endpoints

### Authentication Required:
All endpoints require authentication via Laravel Sanctum.

### Invoice API:
```
POST   /api/invoice          - Create invoice
GET    /api/invoice          - List invoices
GET    /api/invoice/{id}     - Get invoice
PUT    /api/invoice/{id}     - Update invoice
DELETE /api/invoice/{id}     - Delete invoice
POST   /api/invoice/{id}/payment - Add payment
```

### Revenue API:
```
POST   /api/revenue          - Create revenue
GET    /api/revenue          - List revenues
GET    /api/revenue/{id}     - Get revenue
PUT    /api/revenue/{id}     - Update revenue
DELETE /api/revenue/{id}     - Delete revenue
```

### Payment API:
```
POST   /api/payment          - Create payment
GET    /api/payment          - List payments
GET    /api/payment/{id}     - Get payment
PUT    /api/payment/{id}     - Update payment
DELETE /api/payment/{id}     - Delete payment
```

---

## 16. Key Utility Functions

### From `Utility.php`:

```php
// Get account balance
Utility::getAccountBalance($account_id, $start_date, $end_date)

// Add transaction lines
Utility::addTransactionLines($data, $type)

// Get tax data
Utility::getTaxData()

// Calculate tax rate
Utility::taxRate($taxRate, $price, $quantity)

// Invoice number format
Utility::invoiceNumberFormat($settings, $invoice_id)

// Bank account balance update
Utility::bankAccountBalance($id, $amount, $type)
```

---

## 17. Security & Permissions

### Required Permissions:
| Permission | Description |
|------------|-------------|
| manage chart of account | Create/edit/delete COA |
| create bank account | Manage bank accounts |
| manage invoice | Full invoice access |
| manage bill | Full bill access |
| income report | View income reports |
| expense report | View expense reports |
| ledger report | View ledger reports |
| trial balance report | View trial balance |
| statement report | View account statements |

### Implementation:
Permissions are checked in each controller:
```php
if(\Auth::user()->can('manage chart of account')) {
    // Allow access
} else {
    return redirect()->back()->with('error', __('Permission denied.'));
}
```

---

## 18. Troubleshooting

### Common Issues:

**1. Bank Account Balance Not Updating**
- Check if TransactionLines are being created
- Verify chart_account_id is set correctly
- Check Utility::addTransactionLines() is called

**2. Invoice Status Not Changing**
- Ensure payment amount equals invoice total
- For partial payments, status should change to "Partially Paid"

**3. Reports Not Showing Data**
- Verify date range is correct
- Check if transactions have proper account mappings
- Ensure created_by matches current user

**4. Payment Gateway Issues**
- Verify gateway credentials in settings
- Check webhook URL is accessible
- Review gateway-specific documentation

---

## 19. Best Practices

1. **Chart of Accounts Setup**
   - Plan account structure before entering transactions
   - Use consistent account codes
   - Enable/disable accounts rather than deleting

2. **Invoice Management**
   - Send invoices promptly
   - Set appropriate due dates
   - Record payments immediately

3. **Bank Reconciliation**
   - Regularly reconcile bank accounts
   - Review bank statements monthly

4. **Reporting**
   - Generate monthly financial statements
   - Review balance sheet for accuracy
   - Track key financial ratios

5. **Data Backup**
   - Regular database backups
   - Export important reports

---

## 20. Related Documentation

- [General Workflow Documentation](./WORKFLOW_DOCUMENTATION.md)
- [User Management](./USER_MANAGEMENT.md)
- [HRM Module](./HRM_DOCUMENTATION.md)
- [CRM Module](./CRM_DOCUMENTATION.md)
- [API Documentation](./API_DOCUMENTATION.md)

---

*Document Version: 1.0*
*Last Updated: 2024*
*CorporaOne - AI Integrated Business Management System*

