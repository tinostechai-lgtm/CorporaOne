<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Revenue;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Bill;
use App\Models\InvoiceProduct;
use App\Models\BillProduct;
use App\Models\ProductServiceCategory;
use App\Models\Customer;
use App\Models\Vender;
use App\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\Employee;

class ReportController extends Controller
{
    /**
     * Income Summary Report
     */
    public function incomeSummary(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $validator = Validator::make($request->all(), [
            'year' => 'nullable|integer',
            'period' => 'nullable|in:monthly,quarterly,half-yearly,yearly',
            'category' => 'nullable|exists:product_service_categories,id',
            'customer' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $year = $request->year ?? Carbon::now()->year;
        $period = $request->period ?? 'monthly';

        // Revenue + Invoice Income
        $revenues = Revenue::where('created_by', $creatorId)
            ->whereYear('date', $year)
            ->when($request->category, fn($q) => $q->where('category_id', $request->category))
            ->when($request->customer, fn($q) => $q->where('customer_id', $request->customer))
            ->get();

        $invoices = Invoice::where('created_by', $creatorId)
            ->whereYear('send_date', $year)
            ->where('status', '!=', 0)
            ->when($request->customer, fn($q) => $q->where('customer_id', $request->customer))
            ->when($request->category, fn($q) => $q->where('category_id', $request->category))
            ->get();

        $summary = $this->calculateIncomeSummary($revenues, $invoices, $year, $period);

        return response()->json([
            'success' => true,
            'data' => $summary,
            'filters' => [
                'year' => $year,
                'period' => $period,
                'category' => $request->category,
                'customer' => $request->customer,
            ]
        ]);
    }

    /**
     * Expense Summary Report
     */
    public function expenseSummary(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $validator = Validator::make($request->all(), [
            'year' => 'nullable|integer',
            'period' => 'nullable|in:monthly,quarterly,half-yearly,yearly',
            'category' => 'nullable|exists:product_service_categories,id',
            'vender' => 'nullable|exists:venders,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $year = $request->year ?? Carbon::now()->year;

        $payments = Payment::where('created_by', $creatorId)
            ->whereYear('date', $year)
            ->when($request->category, fn($q) => $q->where('category_id', $request->category))
            ->when($request->vender, fn($q) => $q->where('vender_id', $request->vender))
            ->get();

        $bills = Bill::where('created_by', $creatorId)
            ->whereYear('send_date', $year)
            ->where('status', '!=', 0)
            ->when($request->vender, fn($q) => $q->where('vender_id', $request->vender))
            ->when($request->category, fn($q) => $q->where('category_id', $request->category))
            ->get();

        $summary = $this->calculateExpenseSummary($payments, $bills, $year);

        return response()->json([
            'success' => true,
            'data' => $summary,
            'filters' => [
                'year' => $year,
                'category' => $request->category,
                'vender' => $request->vender,
            ]
        ]);
    }

    /**
 * Get all employee leaves with optional filters
 */
public function leave(Request $request)
{
    $user = $request->user();
    $creatorId = $user->creatorId();

    $query = Leave::where('created_by', $creatorId)
        ->with(['employee', 'leaveType']);

    // Filters
    if ($request->has('month')) {
        $query->whereMonth('start_date', $request->month);
    }
    if ($request->has('year')) {
        $query->whereYear('start_date', $request->year);
    }
    if ($request->has('department')) {
        $query->whereHas('employee', function($q) use ($request) {
            $q->where('department_id', $request->department);
        });
    }
    if ($request->has('status')) {
        $query->where('status', $request->status);
    }
    if ($request->has('employee_id')) {
        $query->where('employee_id', $request->employee_id);
    }

    $leaves = $query->get();

    return response()->json([
        'success' => true,
        'data' => $leaves
    ]);
}

/**
 * Get leaves for a specific employee
 */
public function employeeLeave(Request $request, $id)
{
    $user = $request->user();
    $creatorId = $user->creatorId();

    $query = Leave::where('created_by', $creatorId)
        ->where('employee_id', $id)
        ->with(['employee', 'leaveType']);

    if ($request->has('status')) {
        $query->where('status', $request->status);
    }
    if ($request->has('type')) {
        // Filter by leave type name (assuming leaveType has 'name')
        $query->whereHas('leaveType', function($q) use ($request) {
            $q->where('name', $request->type);
        });
    }
    if ($request->has('month')) {
        $query->whereMonth('start_date', $request->month);
    }
    if ($request->has('year')) {
        $query->whereYear('start_date', $request->year);
    }

    $leaves = $query->get();

    return response()->json([
        'success' => true,
        'data' => $leaves
    ]);
}

    /**
     * Income vs Expense Summary
     */
    public function incomeVsExpenseSummary(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $year = $request->year ?? Carbon::now()->year;

        $income = $this->getIncomeData($creatorId, $year, $request->all());
        $expense = $this->getExpenseData($creatorId, $year, $request->all());

        $profit = [];
        for ($i = 1; $i <= 12; $i++) {
            $profit[$i] = ($income['total'][$i] ?? 0) - ($expense['total'][$i] ?? 0);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'income' => $income,
                'expense' => $expense,
                'profit' => $profit,
            ]
        ]);
    }

    /**
     * Tax Summary Report
     */
    public function taxSummary(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $year = $request->year ?? Carbon::now()->year;

        $taxes = Tax::where('created_by', $creatorId)->get();

        $incomeTaxes = $this->calculateTaxData(InvoiceProduct::class, $year, $creatorId);
        $expenseTaxes = $this->calculateTaxData(BillProduct::class, $year, $creatorId);

        return response()->json([
            'success' => true,
            'data' => [
                'taxes' => $taxes,
                'income' => $incomeTaxes,
                'expense' => $expenseTaxes,
            ]
        ]);
    }

    // Helper methods
    private function calculateIncomeSummary($revenues, $invoices, $year, $period)
    {
        // Implement grouping by month/quarter/year as needed
        $monthly = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthly[$i] = [
                'revenue' => $revenues->where('date', 'like', "$year-" . str_pad($i, 2, '0', STR_PAD_LEFT) . "-%")->sum('amount'),
                'invoice' => $invoices->where('send_date', 'like', "$year-" . str_pad($i, 2, '0', STR_PAD_LEFT) . "-%")->sum(fn($inv) => $inv->getTotal()),
            ];
            $monthly[$i]['total'] = $monthly[$i]['revenue'] + $monthly[$i]['invoice'];
        }

        return ['monthly' => $monthly, 'year' => $year];
    }

    private function calculateExpenseSummary($payments, $bills, $year)
    {
        $monthly = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthly[$i] = [
                'payment' => $payments->where('date', 'like', "$year-" . str_pad($i, 2, '0', STR_PAD_LEFT) . "-%")->sum('amount'),
                'bill' => $bills->where('send_date', 'like', "$year-" . str_pad($i, 2, '0', STR_PAD_LEFT) . "-%")->sum(fn($bill) => $bill->getTotal()),
            ];
            $monthly[$i]['total'] = $monthly[$i]['payment'] + $monthly[$i]['bill'];
        }

        return ['monthly' => $monthly, 'year' => $year];
    }

    private function getIncomeData($creatorId, $year, $filters)
    {
        $revenues = Revenue::where('created_by', $creatorId)->whereYear('date', $year)->get();
        $invoices = Invoice::where('created_by', $creatorId)->whereYear('send_date', $year)->where('status', '!=', 0)->get();

        $total = [];
        for ($i = 1; $i <= 12; $i++) {
            $total[$i] = $revenues->whereMonth('date', $i)->sum('amount') +
                         $invoices->whereMonth('send_date', $i)->sum(fn($inv) => $inv->getTotal());
        }

        return ['total' => $total];
    }

    private function getExpenseData($creatorId, $year, $filters)
    {
        $payments = Payment::where('created_by', $creatorId)->whereYear('date', $year)->get();
        $bills = Bill::where('created_by', $creatorId)->whereYear('send_date', $year)->where('status', '!=', 0)->get();

        $total = [];
        for ($i = 1; $i <= 12; $i++) {
            $total[$i] = $payments->whereMonth('date', $i)->sum('amount') +
                         $bills->whereMonth('send_date', $i)->sum(fn($bill) => $bill->getTotal());
        }

        return ['total' => $total];
    }

    private function calculateTaxData($productModel, $year, $creatorId)
    {
        $products = $productModel::whereHas('invoice', fn($q) => $q->where('created_by', $creatorId)->whereYear('send_date', $year))
            ->orWhereHas('bill', fn($q) => $q->where('created_by', $creatorId)->whereYear('send_date', $year))
            ->get();

        $taxSummary = [];
        foreach ($products as $product) {
            if ($product->tax) {
                foreach (explode(',', $product->tax) as $taxId) {
                    $tax = Tax::find($taxId);
                    if ($tax) {
                        $taxAmount = ($tax->rate / 100) * $product->price * $product->quantity;
                        $taxSummary[$tax->name] = ($taxSummary[$tax->name] ?? 0) + $taxAmount;
                    }
                }
            }
        }

        return $taxSummary;
    }
}