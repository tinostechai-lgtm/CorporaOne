<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransactionLines;
use App\Models\Transaction;
use App\Models\BankAccount;
use App\Models\ProductServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransactionExport;

class TransactionController extends Controller
{
    /**
     * List transactions with filters
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $query = TransactionLines::where('created_by', $creatorId)
            ->with('account');

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        // Filter by account
        if ($request->has('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        // Filter by category (if you have a category column)
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by type (debit/credit)
        if ($request->has('type')) {
            if ($request->type == 'debit') {
                $query->where('debit', '>', 0);
            } elseif ($request->type == 'credit') {
                $query->where('credit', '>', 0);
            }
        }

        $transactions = $query->orderBy('date', 'desc')->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $transactions,
            'meta' => [
                'total' => $transactions->total(),
                'per_page' => $transactions->perPage(),
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
            ]
        ]);
    }

    /**
     * Show a single transaction
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        // Try TransactionLines first, then fallback to Transaction
        $transaction = TransactionLines::where('created_by', $creatorId)
            ->with('account')
            ->find($id);

        if (!$transaction) {
            $transaction = Transaction::where('created_by', $creatorId)
                ->with('bankAccount')
                ->find($id);
        }

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found'
            ], 404);
        }

        // Get previous/next for navigation (optional)
        $prev = null;
        $next = null;
        if ($transaction instanceof TransactionLines) {
            $prev = TransactionLines::where('account_id', $transaction->account_id)
                ->where('date', '<', $transaction->date)
                ->orderBy('date', 'desc')
                ->first();

            $next = TransactionLines::where('account_id', $transaction->account_id)
                ->where('date', '>', $transaction->date)
                ->orderBy('date', 'asc')
                ->first();
        }

        return response()->json([
            'success' => true,
            'data' => $transaction,
            'prev' => $prev ? $prev->id : null,
            'next' => $next ? $next->id : null
        ]);
    }

    /**
     * Update a transaction (only for TransactionLines)
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $transaction = TransactionLines::where('created_by', $creatorId)->find($id);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found or not editable'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'date' => 'sometimes|date',
            'description' => 'sometimes|string|max:500',
            'debit' => 'nullable|numeric|min:0',
            'credit' => 'nullable|numeric|min:0',
            'reference' => 'nullable|string|max:100',
            'account_id' => 'nullable|exists:bank_accounts,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $transaction->update($request->only([
            'date', 'description', 'debit', 'credit', 'reference', 'account_id'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Transaction updated successfully',
            'data' => $transaction->fresh('account')
        ]);
    }

    /**
     * Delete a transaction (only for TransactionLines)
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $transaction = TransactionLines::where('created_by', $creatorId)->find($id);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found or cannot be deleted'
            ], 404);
        }

        $transaction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Transaction deleted successfully'
        ]);
    }

    /**
     * Export transactions to Excel (returns download URL or file)
     * For API, we return a download response directly.
     */
    public function export(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        // Apply filters similar to index
        $query = TransactionLines::where('created_by', $creatorId);

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }
        if ($request->has('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        $transactions = $query->orderBy('date', 'desc')->get();

        // Create CSV directly (since we can't use Excel facade easily in API without a file)
        $filename = 'transactions_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Description', 'Account', 'Debit', 'Credit', 'Reference']);

            foreach ($transactions as $txn) {
                fputcsv($file, [
                    $txn->date,
                    $txn->description,
                    $txn->account->name ?? 'N/A',
                    $txn->debit > 0 ? $txn->debit : '',
                    $txn->credit > 0 ? $txn->credit : '',
                    $txn->reference ?? '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get account statement (summary with totals)
     */
    public function statement(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-t'));

        $query = TransactionLines::where('created_by', $creatorId)
            ->whereBetween('date', [$startDate, $endDate]);

        if ($request->has('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        $totalDebit = $query->sum('debit');
        $totalCredit = $query->sum('credit');
        $balance = $totalCredit - $totalDebit;

        $transactions = $query->orderBy('date', 'desc')->paginate(50);

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_debit' => $totalDebit,
                    'total_credit' => $totalCredit,
                    'balance' => $balance,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'transactions' => $transactions
            ]
        ]);
    }
}