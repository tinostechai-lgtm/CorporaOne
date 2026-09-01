<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\TransactionLines;
use App\Models\BankStatementSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BankReconciliationController extends Controller
{
    /**
     * Display the main bank reconciliation index
     */
    public function index()
    {
        $submissions = BankStatementSubmission::orderBy('created_at', 'desc')->get();

        $accounts = ChartOfAccount::where('created_by', Auth::user()->creatorId())
            ->with(['typeAccount', 'subTypeAccount'])
            ->orderBy('code')
            ->get()
            ->map(function ($account) {
                $balanceData = $account->balance();
                return [
                    'id' => $account->id,
                    'name' => $account->name,
                    'code' => $account->code,
                    'type' => optional($account->typeAccount)->name ?? 'Unknown',
                    'balance' => $balanceData['netAmount'] ?? 0,
                ];
            });

        return view('bank-reconciliation.index', compact('submissions', 'accounts'));
    }

    /**
     * Show ledger report for a specific account
     */
    public function ledgerReport(Request $request, $ledgerId = null)
    {
        // If ledgerId is not in route, get from query parameter
        if (!$ledgerId) {
            $ledgerId = $request->input('account');
        }
        
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-t'));
        
        // Get selected account
        $selectedAccount = null;
        if ($ledgerId) {
            $selectedAccount = ChartOfAccount::where('created_by', Auth::user()->creatorId())->find($ledgerId);
        }
        
        // Get accounts for filter dropdown
        $accountsForFilter = ChartOfAccount::where('created_by', Auth::user()->creatorId())
            ->orderBy('code')
            ->get()
            ->map(function ($acc) {
                return [
                    'id' => $acc->id,
                    'code' => $acc->code,
                    'name' => $acc->name,
                ];
            });
        
        // Add "All Accounts" option at the beginning
        $accountsForFilter = collect([['id' => '', 'code' => '', 'name' => 'Select Account']])->concat($accountsForFilter);
        
        // Get bank statements for the upload section
        $bankStatements = BankStatementSubmission::where('created_by', Auth::user()->creatorId())
            ->latest()
            ->take(10)
            ->get();
        
        // Get transactions
        $transactions = collect();
        $totals = ['debit' => 0, 'credit' => 0, 'balance' => 0];
        $selectedAccountId = $ledgerId;
        
        if ($selectedAccountId) {
            try {
                $openingBalance = TransactionLines::where('account_id', $selectedAccountId)
                    ->where('date', '<', $startDate)
                    ->select(DB::raw('COALESCE(SUM(credit), 0) - COALESCE(SUM(debit), 0) as balance'))
                    ->first();
                
                $runningBalance = floatval($openingBalance->balance ?? 0);
                
                $txns = TransactionLines::where('account_id', $selectedAccountId)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->orderBy('date', 'asc')
                    ->get();
                
                foreach ($txns as $txn) {
                    $debit = floatval($txn->debit ?? 0);
                    $credit = floatval($txn->credit ?? 0);
                    $runningBalance += ($credit - $debit);
                    $totals['debit'] += $debit;
                    $totals['credit'] += $credit;
                    
                    $transactions->push((object)[
                        'id' => $txn->id,
                        'date' => $txn->date,
                        'description' => $txn->description ?? $txn->reference ?? 'Transaction',
                        'debit' => $debit,
                        'credit' => $credit,
                        'balance' => $runningBalance,
                        'reference_type' => $txn->reference_type ?? 'Direct',
                        'reference_id' => $txn->reference_id ?? '',
                    ]);
                }
                $totals['balance'] = $runningBalance;
                
            } catch (\Exception $e) {
                Log::warning('TransactionLines failed: ' . $e->getMessage());
            }
        }
        
        $filter = [
            'startDateRange' => $startDate,
            'endDateRange' => $endDate,
        ];
        
        return view('bank-reconciliation.ledger-report', compact(
            'filter',
            'accountsForFilter',
            'selectedAccountId',
            'selectedAccount',
            'transactions',
            'totals',
            'bankStatements'
        ));
    }

    /**
     * Compare ledger with bank statement
     */
    public function compareWithLedger(Request $request)
    {
        $ledgerId = $request->input('ledger_id');
        $submissionId = $request->input('submission_id');
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-t'));
        
        // Get accounts for dropdown
        $accounts = ChartOfAccount::where('created_by', Auth::user()->creatorId())
            ->orderBy('code')
            ->get();
        
        // Get submission
        $submission = null;
        if ($submissionId) {
            $submission = BankStatementSubmission::find($submissionId);
        }
        
        // Get ledger transactions
        $ledgerTransactions = collect();
        if ($ledgerId) {
            $ledgerTransactions = TransactionLines::where('account_id', $ledgerId)
                ->whereBetween('date', [$startDate, $endDate])
                ->orderBy('date', 'asc')
                ->get();
        }
        
        // Get bank transactions from submission
        $bankTransactions = [];
        if ($submission) {
            $bankTransactions = is_array($submission->transactions) 
                ? $submission->transactions 
                : json_decode($submission->transactions, true);
            if (!is_array($bankTransactions)) {
                $bankTransactions = [];
            }
        }
        
        // Perform matching logic
        $comparison = null;
        if ($ledgerId && count($bankTransactions) > 0) {
            $comparison = $this->matchTransactions($ledgerTransactions, $bankTransactions);
        } else {
            $comparison = (object) [
                'total_bank_txs' => 0,
                'total_ledger_txs' => 0,
                'matched_count' => 0,
                'mismatched_count' => 0,
                'unmatched_bank' => 0,
                'unmatched_ledger' => 0,
                'results' => [],
                'match_accuracy' => 0,
                'total_variance' => 0,
                'total_bank_amount' => 0,
                'total_ledger_amount' => 0,
            ];
        }
        
        if ($request->ajax()) {
            return view('bank-reconciliation.compare-content', compact('comparison', 'ledgerId', 'accounts', 'startDate', 'endDate', 'submissionId'))->render();
        }
        
        return view('bank-reconciliation.compare-content', compact('comparison', 'ledgerId', 'accounts', 'startDate', 'endDate', 'submissionId'));
    }

    /**
     * Match ledger transactions with bank transactions
     */
    private function matchTransactions($ledgerTransactions, $bankTransactions)
    {
        $matches = [];
        $unmatchedBank = [];
        $unmatchedLedger = [];
        $usedLedgerIds = [];
        
        foreach ($bankTransactions as $bankTxn) {
            $bestMatch = null;
            $bestScore = 0;
            
            $bankAmount = abs(($bankTxn['debit'] ?? 0) - ($bankTxn['credit'] ?? 0));
            if ($bankAmount == 0) {
                $bankAmount = $bankTxn['amount'] ?? 0;
            }
            $bankDate = isset($bankTxn['date']) ? Carbon::parse($bankTxn['date']) : now();
            $bankDesc = strtolower($bankTxn['description'] ?? $bankTxn['purpose'] ?? '');
            
            foreach ($ledgerTransactions as $ledgerTxn) {
                if (in_array($ledgerTxn->id, $usedLedgerIds)) continue;
                
                $ledgerAmount = abs(($ledgerTxn->debit ?? 0) - ($ledgerTxn->credit ?? 0));
                $ledgerDate = Carbon::parse($ledgerTxn->date);
                $ledgerDesc = strtolower($ledgerTxn->description ?? $ledgerTxn->reference ?? '');
                
                // Amount tolerance: ±1%
                $amountTolerance = $bankAmount * 0.01;
                if (abs($bankAmount - $ledgerAmount) > $amountTolerance && $amountTolerance > 0) continue;
                
                // Date tolerance: ±3 days
                $dateDiff = $bankDate->diffInDays($ledgerDate, false);
                if (abs($dateDiff) > 3) continue;
                
                // Description similarity
                similar_text($bankDesc, $ledgerDesc, $similarity);
                if ($similarity < 30) continue;
                
                $score = $similarity + (100 - abs($dateDiff) * 10);
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = $ledgerTxn;
                }
            }
            
            if ($bestMatch && $bestScore >= 70) {
                $matches[] = [
                    'bank_txn' => $bankTxn,
                    'ledger_txn' => $bestMatch,
                    'score' => $bestScore,
                ];
                $usedLedgerIds[] = $bestMatch->id;
            } else {
                $unmatchedBank[] = $bankTxn;
            }
        }
        
        // Unmatched ledger transactions
        foreach ($ledgerTransactions as $ledgerTxn) {
            if (!in_array($ledgerTxn->id, $usedLedgerIds)) {
                $unmatchedLedger[] = $ledgerTxn;
            }
        }
        
        $results = [];
        
        // Add matched transactions
        foreach ($matches as $match) {
            $bankAmount = abs(($match['bank_txn']['debit'] ?? 0) - ($match['bank_txn']['credit'] ?? 0));
            if ($bankAmount == 0) {
                $bankAmount = $match['bank_txn']['amount'] ?? 0;
            }
            $ledgerAmount = abs(($match['ledger_txn']->debit ?? 0) - ($match['ledger_txn']->credit ?? 0));
            
            // Check if all fields match exactly
            $bankDateStr = isset($match['bank_txn']['date']) ? Carbon::parse($match['bank_txn']['date'])->format('Y-m-d') : null;
            $ledgerDateStr = $match['ledger_txn']->date ? Carbon::parse($match['ledger_txn']->date)->format('Y-m-d') : null;
            $isDateMatch = ($bankDateStr && $ledgerDateStr && $bankDateStr === $ledgerDateStr);
            $isAmountMatch = abs($bankAmount - $ledgerAmount) < 0.01;
            $isDescMatch = strtolower(trim($match['bank_txn']['description'] ?? '')) === strtolower(trim($match['ledger_txn']->description ?? ''));
            
            if ($isDateMatch && $isAmountMatch && $isDescMatch) {
                $status = 'matched';
            } else {
                $status = 'mismatched';
            }
            
            $results[] = (object) [
                'status' => $status,
                'date_formatted' => $bankDateStr ? Carbon::parse($match['bank_txn']['date'])->format('M d, Y') : null,
                'description' => $match['bank_txn']['description'] ?? $match['bank_txn']['purpose'] ?? '-',
                'bank_amount' => $bankAmount,
                'ledger_date' => $match['ledger_txn']->date,
                'ledger_description' => $match['ledger_txn']->description,
                'ledger_amount' => $ledgerAmount,
                'variance' => $bankAmount - $ledgerAmount,
                'match_score' => round($match['score']),
                // Field-level mismatch flags
                'pdf_date_mismatch' => !$isDateMatch,
                'pdf_account_mismatch' => !$isDescMatch,
                'pdf_amount_mismatch' => !$isAmountMatch,
                'ledger_date_mismatch' => !$isDateMatch,
                'ledger_account_mismatch' => !$isDescMatch,
                'ledger_amount_mismatch' => !$isAmountMatch,
                'pdf_reference' => $match['bank_txn']['reference'] ?? null,
                'ledger_reference' => $match['ledger_txn']->reference_no ?? $match['ledger_txn']->reference ?? null,
                'pdf_reference_mismatch' => false,
                'ledger_reference_mismatch' => false,
            ];
        }
        
        // Add bank-only transactions
        foreach ($unmatchedBank as $bankTxn) {
            $bankAmount = abs(($bankTxn['debit'] ?? 0) - ($bankTxn['credit'] ?? 0));
            if ($bankAmount == 0) {
                $bankAmount = $bankTxn['amount'] ?? 0;
            }
            
            $results[] = (object) [
                'status' => 'bank_only',
                'date_formatted' => isset($bankTxn['date']) ? Carbon::parse($bankTxn['date'])->format('M d, Y') : null,
                'description' => $bankTxn['description'] ?? $bankTxn['purpose'] ?? '-',
                'bank_amount' => $bankAmount,
                'ledger_date' => null,
                'ledger_description' => null,
                'ledger_amount' => 0,
                'variance' => $bankAmount,
                'match_score' => 0,
                'pdf_date_mismatch' => true,
                'pdf_account_mismatch' => true,
                'pdf_amount_mismatch' => true,
                'ledger_date_mismatch' => false,
                'ledger_account_mismatch' => false,
                'ledger_amount_mismatch' => false,
                'pdf_reference' => $bankTxn['reference'] ?? null,
                'ledger_reference' => null,
                'pdf_reference_mismatch' => false,
                'ledger_reference_mismatch' => false,
            ];
        }
        
        // Add ledger-only transactions
        foreach ($unmatchedLedger as $ledgerTxn) {
            $ledgerAmount = abs(($ledgerTxn->debit ?? 0) - ($ledgerTxn->credit ?? 0));
            
            $results[] = (object) [
                'status' => 'ledger_only',
                'date_formatted' => null,
                'description' => null,
                'bank_amount' => 0,
                'ledger_date' => $ledgerTxn->date,
                'ledger_description' => $ledgerTxn->description,
                'ledger_amount' => $ledgerAmount,
                'variance' => -$ledgerAmount,
                'match_score' => 0,
                'pdf_date_mismatch' => false,
                'pdf_account_mismatch' => false,
                'pdf_amount_mismatch' => false,
                'ledger_date_mismatch' => true,
                'ledger_account_mismatch' => true,
                'ledger_amount_mismatch' => true,
                'pdf_reference' => null,
                'ledger_reference' => $ledgerTxn->reference_no ?? $ledgerTxn->reference ?? null,
                'pdf_reference_mismatch' => false,
                'ledger_reference_mismatch' => false,
            ];
        }
        
        // Sort results by date (newest first)
        usort($results, function($a, $b) {
            $dateA = $a->date_formatted ?? $a->ledger_date ?? '';
            $dateB = $b->date_formatted ?? $b->ledger_date ?? '';
            return strtotime($dateB) - strtotime($dateA);
        });
        
        // Calculate totals
        $totalBankAmount = collect($results)->sum('bank_amount');
        $totalLedgerAmount = collect($results)->sum('ledger_amount');
        
        $matchedCount = collect($results)->where('status', 'matched')->count();
        $mismatchedCount = collect($results)->where('status', 'mismatched')->count();
        $unmatchedBankCount = collect($results)->where('status', 'bank_only')->count();
        $unmatchedLedgerCount = collect($results)->where('status', 'ledger_only')->count();
        
        return (object) [
            'total_bank_txs' => $totalBankAmount,
            'total_ledger_txs' => $totalLedgerAmount,
            'matched_count' => $matchedCount,
            'mismatched_count' => $mismatchedCount,
            'unmatched_bank' => $unmatchedBankCount,
            'unmatched_ledger' => $unmatchedLedgerCount,
            'results' => $results,
            'match_accuracy' => count($bankTransactions) > 0 ? round((($matchedCount + $mismatchedCount) / count($bankTransactions)) * 100, 1) : 0,
            'total_variance' => collect($results)->sum('variance'),
            'total_bank_amount' => $totalBankAmount,
            'total_ledger_amount' => $totalLedgerAmount,
        ];
    }

    /**
     * Update ledger entry
     */
    public function updateLedger(Request $request, $id)
    {
        try {
            Log::info('Updating ledger entry', ['id' => $id, 'data' => $request->all()]);
            
            $ledger = TransactionLines::findOrFail($id);
            
            $ledger->update([
                'date' => $request->date,
                'description' => $request->description,
                'debit' => $request->debit ?? 0,
                'credit' => $request->credit ?? 0,
                'reference' => $request->reference,
                'updated_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Ledger entry updated successfully',
                'data' => $ledger
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating ledger: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete ledger entry
     */
    public function deleteLedger($id)
    {
        try {
            Log::info('Deleting ledger entry', ['id' => $id]);
            
            $ledger = TransactionLines::findOrFail($id);
            $ledger->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Ledger entry deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting ledger: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add transaction to ledger from bank statement
     */
    public function addToLedger(Request $request)
    {
        try {
            Log::info('Adding transaction to ledger', $request->all());
            
            $ledger = TransactionLines::create([
                'account_id' => $request->ledger_id,
                'date' => $request->date,
                'description' => $request->description,
                'debit' => $request->debit ?? 0,
                'credit' => $request->credit ?? 0,
                'amount' => $request->amount,
                'reference' => $request->reference ?? '',
                'created_by' => auth()->user()->creatorId(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Transaction added successfully',
                'data' => $ledger
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error adding to ledger: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Match transaction with bank statement
     */
    public function matchTransaction(Request $request)
    {
        try {
            Log::info('Matching transaction', $request->all());
            
            $ledger = TransactionLines::findOrFail($request->ledger_id);
            
            // Create or update reconciliation record
            DB::table('reconciliations')->updateOrInsert(
                [
                    'ledger_id' => $ledger->id,
                    'bank_transaction_ref' => $request->bank_transaction['reference'] ?? null,
                ],
                [
                    'matched_at' => now(),
                    'matched_by' => auth()->id(),
                    'bank_amount' => $request->bank_transaction['amount'],
                    'bank_date' => $request->bank_transaction['date'],
                    'bank_description' => $request->bank_transaction['description'],
                    'status' => 'matched',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Transaction matched successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error matching transaction: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ignore transaction
     */
    public function ignoreTransaction(Request $request)
    {
        try {
            Log::info('Ignoring transaction', $request->all());
            
            // Store ignored transactions
            DB::table('ignored_transactions')->insert([
                'transaction_ref' => $request->reference ?? md5(json_encode($request->all())),
                'transaction_data' => json_encode($request->all()),
                'ignored_by' => auth()->id(),
                'ignored_at' => now(),
                'created_at' => now(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Transaction ignored successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error ignoring transaction: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update comparison entry (for the comparison page)
     */
    public function updateComparisonEntry(Request $request)
    {
        try {
            Log::info('Updating comparison entry', $request->all());
            
            if ($request->status == 'bank_only') {
                // Update bank statement transaction
                DB::table('bank_statement_transactions')
                    ->where('id', $request->bank_txn_id)
                    ->update([
                        'date' => $request->date,
                        'description' => $request->description,
                        'amount' => $request->amount,
                        'reference' => $request->reference,
                        'updated_at' => now()
                    ]);
            } elseif ($request->status == 'ledger_only') {
                // Update ledger transaction
                TransactionLines::where('id', $request->ledger_txn_id)
                    ->update([
                        'date' => $request->date,
                        'description' => $request->description,
                        'amount' => $request->amount,
                        'reference' => $request->reference,
                        'updated_at' => now()
                    ]);
            }
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            Log::error('Error updating comparison entry: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manual match transactions (for the comparison page)
     */
    public function manualMatch(Request $request)
    {
        try {
            Log::info('Manual matching transactions', $request->all());
            
            // Create reconciliation record
            DB::table('reconciliations')->insert([
                'submission_id' => $request->submission_id,
                'bank_txn_id' => $request->bank_txn_id,
                'ledger_txn_id' => $request->ledger_txn_id,
                'matched_by' => auth()->id(),
                'matched_at' => now(),
                'created_at' => now()
            ]);
            
            // Update status in bank_statement_transactions if exists
            if ($request->bank_txn_id) {
                DB::table('bank_statement_transactions')
                    ->where('id', $request->bank_txn_id)
                    ->update(['reconciled' => 1, 'reconciled_at' => now()]);
            }
            
            // Update ledger transaction if exists
            if ($request->ledger_txn_id) {
                TransactionLines::where('id', $request->ledger_txn_id)
                    ->update(['reconciled' => 1, 'reconciled_at' => now()]);
            }
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            Log::error('Error in manual match: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete comparison entry (for the comparison page)
     */
    public function deleteComparisonEntry(Request $request)
    {
        try {
            Log::info('Deleting comparison entry', $request->all());
            
            if ($request->status == 'bank_only' && $request->bank_txn_id) {
                DB::table('bank_statement_transactions')
                    ->where('id', $request->bank_txn_id)
                    ->delete();
            } elseif ($request->status == 'ledger_only' && $request->ledger_txn_id) {
                TransactionLines::where('id', $request->ledger_txn_id)
                    ->delete();
            }
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting comparison entry: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reconcile all transactions (for the comparison page)
     */
    public function reconcileAll(Request $request)
    {
        try {
            Log::info('Reconciling all transactions', $request->all());
            
            DB::table('reconciliations')
                ->where('submission_id', $request->submission_id)
                ->update(['reconciled_at' => now()]);
            
            // Also update bank transactions
            DB::table('bank_statement_transactions')
                ->where('submission_id', $request->submission_id)
                ->update(['reconciled' => 1, 'reconciled_at' => now()]);
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            Log::error('Error reconciling all: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview ledger transactions via AJAX
     */
    public function previewLedgerTransactions(Request $request)
    {
        try {
            $accountId = $request->input('account_id');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            
            $transactions = TransactionLines::where('account_id', $accountId)
                ->whereBetween('date', [$startDate, $endDate])
                ->orderBy('date', 'desc')
                ->limit(50)
                ->get()
                ->map(function ($txn) {
                    return [
                        'id' => $txn->id,
                        'date' => $txn->date,
                        'description' => $txn->description,
                        'debit' => $txn->debit,
                        'credit' => $txn->credit,
                        'balance' => $txn->debit + $txn->credit,
                        'reference' => $txn->reference
                    ];
                });
            
            return response()->json($transactions);
            
        } catch (\Exception $e) {
            Log::error('Error previewing ledger: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }
}