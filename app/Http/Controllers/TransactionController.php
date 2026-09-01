<?php

namespace App\Http\Controllers;

use App\Exports\TransactionExport;
use App\Models\BankAccount;
use App\Models\ProductServiceCategory;
use App\Models\Transaction;
use App\Models\TransactionLines;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class TransactionController extends Controller
{

    public function index(Request $request)
    {
        if(\Auth::user()->can('manage transaction'))
        {
            $filter['account']  = __('All');
            $filter['category'] = __('All');

            $account = BankAccount::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('holder_name', 'id');
            $account->prepend(__('Stripe / Paypal'), 'strip-paypal');
            $account->prepend('Select Account', '');

            $accounts = Transaction::select('bank_accounts.id', 'bank_accounts.holder_name', 'bank_accounts.bank_name')
                                   ->leftjoin('bank_accounts', 'transactions.account', '=', 'bank_accounts.id')
                                   ->groupBy('transactions.account')->selectRaw('sum(amount) as total');

            $category = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->whereIn(
                'type', [
                          1,
                          2,
                      ]
            )->get()->pluck('name', 'name');

            $category->prepend('Invoice', 'Invoice');
            $category->prepend('Bill', 'Bill');
            $category->prepend('Select Category', '');

            $transactions = Transaction::orderBy('id', 'desc');

            if(!empty($request->start_month) && !empty($request->end_month))
            {
                $start = strtotime($request->start_month);
                $end   = strtotime($request->end_month);
            }
            else
            {
                $start = strtotime(date('Y-m'));
                $end   = strtotime(date('Y-m', strtotime("-5 month")));
            }

            $currentdate = $start;

            while($currentdate <= $end)
            {
                $data['month'] = date('m', $currentdate);
                $data['year']  = date('Y', $currentdate);

                $transactions->Orwhere(
                    function ($query) use ($data){
                        $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                        $query->where('transactions.created_by', '=', \Auth::user()->creatorId());
                    }
                );

                $accounts->Orwhere(
                    function ($query) use ($data){
                        $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                        $query->where('transactions.created_by', '=', \Auth::user()->creatorId());
                    }
                );

                $currentdate = strtotime('+1 month', $currentdate);
            }

            $filter['startDateRange'] = date('M-Y', $start);
            $filter['endDateRange']   = date('M-Y', $end);

            if(!empty($request->account))
            {
                $transactions->where('account', $request->account);

                if($request->account == 'strip-paypal')
                {
                    $accounts->where('account', 0);
                    $filter['account'] = __('Stripe / Paypal');
                }
                else
                {
                    $accounts->where('account', $request->account);
                    $bankAccount       = BankAccount::find($request->account);
                    $filter['account'] = !empty($bankAccount) ? $bankAccount->holder_name . ' - ' . $bankAccount->bank_name : '';
                    if($bankAccount && $bankAccount->holder_name == 'Cash')
                    {
                        $filter['account'] = 'Cash';
                    }
                }
            }
            
            if(!empty($request->category))
            {
                $transactions->where('category', $request->category);
                $accounts->where('category', $request->category);

                $filter['category'] = $request->category;
            }

            $transactions->where('created_by', '=', \Auth::user()->creatorId());
            $accounts->where('transactions.created_by', '=', \Auth::user()->creatorId());
            $transactions = $transactions->with(['bankAccount'])->get();
            $accounts     = $accounts->get();

            return view('transaction.index', compact('transactions', 'account', 'category', 'filter', 'accounts'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show transaction details for a specific transaction
     */
    public function show($id)
    {
        try {
            // Try to find in TransactionLines first (accounting system)
            $transaction = TransactionLines::with('account')->find($id);
            
            if (!$transaction) {
                // If not found, try the old Transaction model
                $transaction = Transaction::with('bankAccount')->find($id);
            }
            
            if (!$transaction) {
                return redirect()->back()->with('error', __('Transaction not found.'));
            }
            
            // Get previous and next transactions for navigation
            $prevTransaction = null;
            $nextTransaction = null;
            
            if ($transaction instanceof TransactionLines) {
                $prevTransaction = TransactionLines::where('account_id', $transaction->account_id)
                    ->where('date', '<', $transaction->date)
                    ->orderBy('date', 'desc')
                    ->first();
                
                $nextTransaction = TransactionLines::where('account_id', $transaction->account_id)
                    ->where('date', '>', $transaction->date)
                    ->orderBy('date', 'asc')
                    ->first();
            }
            
            return view('transaction.show', compact('transaction', 'prevTransaction', 'nextTransaction'));
            
        } catch (\Exception $e) {
            Log::error('Error showing transaction: ' . $e->getMessage());
            return redirect()->back()->with('error', __('Transaction not found.'));
        }
    }

    /**
     * Edit transaction
     */
    public function edit($id)
    {
        try {
            $transaction = TransactionLines::findOrFail($id);
            
            // Get accounts for dropdown
            $accounts = BankAccount::where('created_by', \Auth::user()->creatorId())
                ->pluck('holder_name', 'id');
            
            return view('transaction.edit', compact('transaction', 'accounts'));
            
        } catch (\Exception $e) {
            Log::error('Error editing transaction: ' . $e->getMessage());
            return redirect()->back()->with('error', __('Transaction not found.'));
        }
    }

    /**
     * Update transaction
     */
    public function update(Request $request, $id)
    {
        try {
            $transaction = TransactionLines::findOrFail($id);
            
            $validated = $request->validate([
                'date' => 'required|date',
                'description' => 'required|string|max:500',
                'debit' => 'nullable|numeric|min:0',
                'credit' => 'nullable|numeric|min:0',
                'reference' => 'nullable|string|max:100',
                'account_id' => 'nullable|exists:bank_accounts,id',
            ]);
            
            $transaction->update([
                'date' => $request->date,
                'description' => $request->description,
                'debit' => $request->debit ?? 0,
                'credit' => $request->credit ?? 0,
                'reference' => $request->reference,
                'account_id' => $request->account_id ?? $transaction->account_id,
                'updated_at' => now(),
                'updated_by' => \Auth::user()->id,
            ]);
            
            return redirect()->route('transaction.show', $id)
                ->with('success', __('Transaction updated successfully.'));
                
        } catch (\Exception $e) {
            Log::error('Error updating transaction: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', __('Failed to update transaction: ') . $e->getMessage());
        }
    }

    /**
     * Delete transaction
     */
    public function destroy($id)
    {
        try {
            $transaction = TransactionLines::findOrFail($id);
            $transaction->delete();
            
            return response()->json([
                'success' => true,
                'message' => __('Transaction deleted successfully.')
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting transaction: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => __('Failed to delete transaction.')
            ], 500);
        }
    }

    /**
     * Get all transactions for a specific account
     */
    public function accountTransactions($accountId, Request $request)
    {
        try {
            $startDate = $request->input('start_date', date('Y-m-01'));
            $endDate = $request->input('end_date', date('Y-m-t'));
            
            $transactions = TransactionLines::where('account_id', $accountId)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('created_by', \Auth::user()->creatorId())
                ->orderBy('date', 'desc')
                ->paginate(50);
            
            $totalDebit = $transactions->sum('debit');
            $totalCredit = $transactions->sum('credit');
            $balance = $totalCredit - $totalDebit;
            
            $account = BankAccount::find($accountId);
            
            return view('transaction.account-transactions', compact(
                'transactions', 'accountId', 'account', 'totalDebit', 
                'totalCredit', 'balance', 'startDate', 'endDate'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error loading account transactions: ' . $e->getMessage());
            return redirect()->back()->with('error', __('Failed to load transactions.'));
        }
    }

    /**
     * Get account statement report with filter
     */
    public function accountStatement(Request $request)
    {
        try {
            $startMonth = $request->input('start_month', now()->subMonths(5)->format('Y-m'));
            $endMonth = $request->input('end_month', now()->format('Y-m'));
            $type = $request->input('type', 'all');
            
            $startDate = date('Y-m-01', strtotime($startMonth . '-01'));
            $endDate = date('Y-m-t', strtotime($endMonth . '-01'));
            
            $query = TransactionLines::with('account')
                ->whereBetween('date', [$startDate, $endDate])
                ->where('created_by', \Auth::user()->creatorId());
            
            if ($type == 'debit') {
                $query->where('debit', '>', 0);
            } elseif ($type == 'credit') {
                $query->where('credit', '>', 0);
            }
            
            $transactions = $query->orderBy('date', 'desc')->paginate(50);
            
            $totalDebit = $query->sum('debit');
            $totalCredit = $query->sum('credit');
            $balance = $totalCredit - $totalDebit;
            
            // Get accounts for filter
            $bankAccounts = BankAccount::where('created_by', \Auth::user()->creatorId())
                ->pluck('holder_name', 'id');
            
            return view('report.account-statement-report', compact(
                'transactions', 'totalDebit', 'totalCredit', 'balance', 
                'bankAccounts', 'startMonth', 'endMonth', 'type'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error loading account statement: ' . $e->getMessage());
            return redirect()->back()->with('error', __('Failed to load account statement.'));
        }
    }

    /**
     * Export transactions to Excel
     */
    public function export()
    {
        $name = 'transaction_' . date('Y-m-d H:i:s');
        $data = Excel::download(new TransactionExport(), $name . '.xlsx');
        return $data;
    }

    /**
     * Export account statement to CSV
     */
    public function exportStatement(Request $request)
    {
        try {
            $startDate = $request->input('start_date', date('Y-m-01'));
            $endDate = $request->input('end_date', date('Y-m-t'));
            $accountId = $request->input('account_id');
            
            $query = TransactionLines::with('account')
                ->whereBetween('date', [$startDate, $endDate])
                ->where('created_by', \Auth::user()->creatorId());
            
            if ($accountId) {
                $query->where('account_id', $accountId);
            }
            
            $transactions = $query->orderBy('date', 'desc')->get();
            
            $filename = 'account_statement_' . date('Y-m-d') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];
            
            $callback = function() use ($transactions) {
                $file = fopen('php://output', 'w');
                
                // Add headers
                fputcsv($file, ['Date', 'Description', 'Account', 'Debit', 'Credit', 'Amount', 'Reference']);
                
                // Add data
                foreach ($transactions as $txn) {
                    fputcsv($file, [
                        $txn->date,
                        $txn->description,
                        $txn->account->name ?? 'N/A',
                        $txn->debit > 0 ? $txn->debit : '',
                        $txn->credit > 0 ? $txn->credit : '',
                        $txn->debit + $txn->credit,
                        $txn->reference ?? '',
                    ]);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
            
        } catch (\Exception $e) {
            Log::error('Error exporting statement: ' . $e->getMessage());
            return redirect()->back()->with('error', __('Failed to export statement.'));
        }
    }
}