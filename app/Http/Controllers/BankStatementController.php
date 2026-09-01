<?php

namespace App\Http\Controllers;

use App\Models\BankStatementSubmission;
use App\Models\ChartOfAccount;
use App\Models\TransactionLines;
use App\Services\BankStatementExtractionService;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class BankStatementController extends Controller
{
    protected BankStatementExtractionService $extractionService;
    protected FileUploadService $fileUploadService;

    public function __construct(
        BankStatementExtractionService $extractionService,
        FileUploadService $fileUploadService
    ) {
        $this->extractionService = $extractionService;
        $this->fileUploadService = $fileUploadService;
    }

    public function index()
    {
        $submissions = BankStatementSubmission::where('created_by', Auth::user()->creatorId())
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('bank-statement.index', compact('submissions'));
    }

    public function create()
    {
        return view('bank-statement.create');
    }

    public function store(Request $request)
    {
        \Log::info('Store method called', $request->all());
        
        try {
            $request->validate([
                'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
            ]);
            
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $storedName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $originalName);
            $filePath = $file->storeAs('bank-statements', $storedName, 'public');
            
            $submission = BankStatementSubmission::create([
                'bank_name' => $request->input('bank_name', 'Uploaded Statement'),
                'account_number' => $request->input('account_number'),
                'original_file_name' => $originalName,
                'stored_file_name' => $storedName,
                'file_path' => $filePath,
                'status' => 'pending',
                'reconciliation_status' => 'pending',
                'created_by' => Auth::user()->creatorId(),
                'transactions' => json_encode([]) // Initialize empty
            ]);
            
            // Dispatch extraction job
            \App\Jobs\ProcessBankStatement::dispatch($submission->id)->onQueue('bank_statements');
            
            \Log::info('✅ Submission created & job dispatched', ['id' => $submission->id]);
            
            return response()->json([
                'success' => true,
                'id' => $submission->id,
                'message' => '✅ Upload successful! Extraction queued.',
                'extraction_status' => 'queued',
                'check_url' => route('bank-statement.show', $submission->id)
            ]);
            
        } catch (\Exception $e) {
            \Log::error('❌ Store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '❌ Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manual extraction trigger (for failed/pending statements)
     */
    public function extract($id)
    {
        try {
            $submission = BankStatementSubmission::where('id', $id)
                ->where('created_by', Auth::user()->creatorId())
                ->firstOrFail();
                
            if ($submission->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Already extracted!'
                ]);
            }
            
            // Re-run extraction job
            \App\Jobs\ProcessBankStatement::dispatch($id);
            
            return response()->json([
                'success' => true,
                'message' => 'Extraction re-triggered!'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $submission = BankStatementSubmission::where('id', $id)
            ->where('created_by', Auth::user()->creatorId())
            ->firstOrFail();
        
        // Calculate summary statistics
        $transactions = $submission->transactions ?? [];
        $totalDebit = collect($transactions)->sum('debit');
        $totalCredit = collect($transactions)->sum('credit');
        $totalTransactions = count($transactions);
        $matchRate = $submission->match_rate ?? 0;
            
        return view('bank-statement.show', compact('submission', 'totalDebit', 'totalCredit', 'totalTransactions', 'matchRate'));
    }

    /**
     * Compare statement with ledger - This is the endpoint called from the modal
     */
    public function compare($id, Request $request)
    {
        $submission = BankStatementSubmission::where('id', $id)
            ->where('created_by', Auth::user()->creatorId())
            ->firstOrFail();
            
        $ledgerId = $request->input('ledger_id');
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-t'));
        
        // Get available ledger accounts for dropdown
        $accounts = ChartOfAccount::where('created_by', Auth::user()->creatorId())
            ->orderBy('code')
            ->get();
        
        // Get ledger transactions if account selected
        $ledgerTransactions = collect();
        $comparison = null;
        
        if ($ledgerId) {
            $ledgerTransactions = $this->getLedgerTransactions($ledgerId, $startDate, $endDate);
            $bankTransactions = $submission->transactions ?? [];
            $comparison = $this->compareTransactions($bankTransactions, $ledgerTransactions);
        }
        
        return view('bank-statement.compare', compact(
            'submission', 
            'accounts', 
            'ledgerId', 
            'startDate', 
            'endDate',
            'ledgerTransactions',
            'comparison'
        ));
    }
    
    /**
     * Get ledger transactions for comparison
     */
    private function getLedgerTransactions($ledgerId, $startDate, $endDate)
    {
        if (!$ledgerId) {
            return collect();
        }
        
        return TransactionLines::select(
            'transaction_lines.*',
            'chart_of_accounts.name as account_name',
            'chart_of_accounts.code as account_code'
        )
        ->leftJoin('chart_of_accounts', 'transaction_lines.account_id', '=', 'chart_of_accounts.id')
        ->where('transaction_lines.account_id', $ledgerId)
        ->where('transaction_lines.created_by', Auth::user()->creatorId())
        ->whereBetween('transaction_lines.date', [$startDate, $endDate])
        ->orderBy('transaction_lines.date', 'desc')
        ->get();
    }
    
    /**
     * Compare bank transactions with ledger transactions with field-level details
     */
    private function compareTransactions($bankTransactions, $ledgerTransactions)
    {
        $results = [];
        $matched = [];
        $unmatchedBank = [];
        $unmatchedLedger = [];
        
        $ledgerCollection = collect($ledgerTransactions);
        $usedLedgerIds = [];
        
        foreach ($bankTransactions as $index => $bankTx) {
            $bestMatch = null;
            $bestScore = 0;
            $bestLedgerIndex = null;
            
            $bankAmount = ($bankTx['debit'] ?? 0) + ($bankTx['credit'] ?? 0);
            $bankDate = $bankTx['date'] ?? '';
            $bankDescription = strtolower($bankTx['description'] ?? '');
            $bankReference = $bankTx['reference'] ?? $bankTx['cheque_number'] ?? '';
            
            foreach ($ledgerCollection as $ledgerIndex => $ledgerTx) {
                if (in_array($ledgerTx->id, $usedLedgerIds)) {
                    continue;
                }
                
                $ledgerAmount = ($ledgerTx->debit ?? 0) + ($ledgerTx->credit ?? 0);
                $ledgerDate = $ledgerTx->date ?? '';
                $ledgerDescription = strtolower($ledgerTx->description ?? $ledgerTx->reference ?? '');
                $ledgerReference = $ledgerTx->reference_no ?? $ledgerTx->cheque_no ?? '';
                
                $score = $this->calculateMatchScore(
                    $bankAmount, $bankDate, $bankDescription,
                    $ledgerAmount, $ledgerDate, $ledgerDescription
                );
                
                if ($score > $bestScore && $score >= 60) {
                    $bestScore = $score;
                    $bestMatch = $ledgerTx;
                    $bestLedgerIndex = $ledgerIndex;
                }
            }
            
            if ($bestMatch) {
                // Create detailed comparison result with field-level mismatches
                $result = $this->createComparisonResult($bankTx, $bestMatch, $bestScore);
                $results[] = $result;
                $matched[] = [
                    'bank_transaction' => $bankTx,
                    'ledger_transaction' => $bestMatch,
                    'match_score' => $bestScore,
                    'match_type' => $this->getMatchType($bestScore),
                    'bank_amount' => $bankAmount,
                    'ledger_amount' => ($bestMatch->debit ?? 0) + ($bestMatch->credit ?? 0),
                    'comparison_result' => $result
                ];
                $usedLedgerIds[] = $bestMatch->id;
                $ledgerCollection->forget($bestLedgerIndex);
            } else {
                // Create result for bank-only transaction
                $result = $this->createBankOnlyResult($bankTx);
                $results[] = $result;
                $unmatchedBank[] = $bankTx;
            }
        }
        
        // Get remaining unmatched ledger transactions
        foreach ($ledgerCollection as $ledgerTx) {
            $result = $this->createLedgerOnlyResult($ledgerTx);
            $results[] = $result;
            $unmatchedLedger[] = $ledgerTx;
        }
        
        // Sort results by date (newest first)
        usort($results, function($a, $b) {
            $dateA = $a->pdf_date ?? $a->ledger_date ?? '';
            $dateB = $b->pdf_date ?? $b->ledger_date ?? '';
            return strtotime($dateB) - strtotime($dateA);
        });
        
        // Calculate totals
        $totalBankAmount = collect($bankTransactions)->sum(function($tx) {
            return ($tx['debit'] ?? 0) + ($tx['credit'] ?? 0);
        });
        
        $totalMatchedAmount = collect($matched)->sum(function($match) {
            return $match['bank_amount'];
        });
        
        $totalLedgerAmount = $ledgerTransactions->sum(function($tx) {
            return ($tx->debit ?? 0) + ($tx->credit ?? 0);
        });
        
        $matchedCount = collect($results)->filter(function($result) {
            return $result->status === 'Matched';
        })->count();
        
        $mismatchedCount = collect($results)->filter(function($result) {
            return $result->status === 'Mismatched';
        })->count();
        
        return [
            'results' => $results,
            'matched' => $matched,
            'unmatched_bank' => $unmatchedBank,
            'unmatched_ledger' => $unmatchedLedger,
            'total_matched' => count($matched),
            'total_bank' => count($bankTransactions),
            'total_ledger' => count($ledgerTransactions),
            'total_bank_amount' => $totalBankAmount,
            'total_ledger_amount' => $totalLedgerAmount,
            'total_matched_amount' => $totalMatchedAmount,
            'match_rate' => $totalBankAmount > 0 ? round(($totalMatchedAmount / $totalBankAmount) * 100, 2) : 0,
            'difference' => $totalBankAmount - $totalLedgerAmount,
            'matched_count' => $matchedCount,
            'mismatched_count' => $mismatchedCount,
            'unmatched_bank_count' => count($unmatchedBank),
            'unmatched_ledger_count' => count($unmatchedLedger),
            'match_accuracy' => $totalBankAmount > 0 ? round(($totalMatchedAmount / $totalBankAmount) * 100, 2) : 0
        ];
    }

    /**
     * Create detailed comparison result for matched transactions with field-level mismatches
     */
    private function createComparisonResult($bankTx, $ledgerTx, $matchScore)
    {
        $result = new \stdClass();
        
        // PDF (Bank Statement) fields
        $result->pdf_date = $bankTx['date'] ?? null;
        $result->pdf_account = $bankTx['description'] ?? null;
        $result->pdf_amount = ($bankTx['debit'] ?? 0) + ($bankTx['credit'] ?? 0);
        $result->pdf_reference = $bankTx['reference'] ?? $bankTx['cheque_number'] ?? null;
        
        // Ledger (Raw) fields
        $result->ledger_date = $ledgerTx->date ?? null;
        $result->ledger_account = $ledgerTx->description ?? $ledgerTx->account_name ?? null;
        $result->ledger_amount = ($ledgerTx->debit ?? 0) + ($ledgerTx->credit ?? 0);
        $result->ledger_reference = $ledgerTx->reference_no ?? $ledgerTx->cheque_no ?? null;
        
        // Calculate field-level mismatches
        $result->pdf_date_mismatch = !$this->isDateMatch($result->pdf_date, $result->ledger_date);
        $result->pdf_account_mismatch = !$this->isTextMatch($result->pdf_account, $result->ledger_account);
        $result->pdf_amount_mismatch = !$this->isAmountMatch($result->pdf_amount, $result->ledger_amount);
        $result->pdf_reference_mismatch = !$this->isTextMatch($result->pdf_reference, $result->ledger_reference);
        
        $result->ledger_date_mismatch = $result->pdf_date_mismatch;
        $result->ledger_account_mismatch = $result->pdf_account_mismatch;
        $result->ledger_amount_mismatch = $result->pdf_amount_mismatch;
        $result->ledger_reference_mismatch = $result->pdf_reference_mismatch;
        
        // Determine overall status
        if ($result->pdf_date_mismatch || $result->pdf_account_mismatch || $result->pdf_amount_mismatch) {
            $result->status = 'Mismatched';
        } else {
            $result->status = 'Matched';
        }
        
        $result->match_score = $matchScore;
        
        return $result;
    }

    /**
     * Create result for bank-only transaction (exists only in PDF)
     */
    private function createBankOnlyResult($bankTx)
    {
        $result = new \stdClass();
        
        // PDF (Bank Statement) fields
        $result->pdf_date = $bankTx['date'] ?? null;
        $result->pdf_account = $bankTx['description'] ?? null;
        $result->pdf_amount = ($bankTx['debit'] ?? 0) + ($bankTx['credit'] ?? 0);
        $result->pdf_reference = $bankTx['reference'] ?? $bankTx['cheque_number'] ?? null;
        
        // Ledger fields - all null (missing)
        $result->ledger_date = null;
        $result->ledger_account = null;
        $result->ledger_amount = null;
        $result->ledger_reference = null;
        
        // Mismatch flags - mark as missing
        $result->pdf_date_mismatch = true;
        $result->pdf_account_mismatch = true;
        $result->pdf_amount_mismatch = true;
        $result->pdf_reference_mismatch = !empty($result->pdf_reference);
        
        $result->ledger_date_mismatch = false;
        $result->ledger_account_mismatch = false;
        $result->ledger_amount_mismatch = false;
        $result->ledger_reference_mismatch = false;
        
        $result->status = 'PDF Only';
        $result->match_score = 0;
        
        return $result;
    }

    /**
     * Create result for ledger-only transaction (exists only in ledger)
     */
    private function createLedgerOnlyResult($ledgerTx)
    {
        $result = new \stdClass();
        
        // PDF fields - all null (missing)
        $result->pdf_date = null;
        $result->pdf_account = null;
        $result->pdf_amount = null;
        $result->pdf_reference = null;
        
        // Ledger (Raw) fields
        $result->ledger_date = $ledgerTx->date ?? null;
        $result->ledger_account = $ledgerTx->description ?? $ledgerTx->account_name ?? null;
        $result->ledger_amount = ($ledgerTx->debit ?? 0) + ($ledgerTx->credit ?? 0);
        $result->ledger_reference = $ledgerTx->reference_no ?? $ledgerTx->cheque_no ?? null;
        
        // Mismatch flags - mark as missing
        $result->pdf_date_mismatch = false;
        $result->pdf_account_mismatch = false;
        $result->pdf_amount_mismatch = false;
        $result->pdf_reference_mismatch = false;
        
        $result->ledger_date_mismatch = true;
        $result->ledger_account_mismatch = true;
        $result->ledger_amount_mismatch = true;
        $result->ledger_reference_mismatch = !empty($result->ledger_reference);
        
        $result->status = 'Ledger Only';
        $result->match_score = 0;
        
        return $result;
    }

    /**
     * Check if dates match (allow for format differences)
     */
    private function isDateMatch($date1, $date2)
    {
        if (empty($date1) || empty($date2)) {
            return false;
        }
        
        try {
            $timestamp1 = strtotime($date1);
            $timestamp2 = strtotime($date2);
            
            if ($timestamp1 && $timestamp2) {
                return date('Y-m-d', $timestamp1) === date('Y-m-d', $timestamp2);
            }
        } catch (\Exception $e) {
            return false;
        }
        
        return false;
    }

    /**
     * Check if text matches (case-insensitive, trimmed, normalized)
     */
    private function isTextMatch($text1, $text2)
    {
        if (empty($text1) && empty($text2)) {
            return true;
        }
        
        if (empty($text1) || empty($text2)) {
            return false;
        }
        
        // Normalize strings: remove special characters, convert to lowercase
        $normalized1 = trim(strtolower(preg_replace('/[^a-z0-9]/', '', $text1)));
        $normalized2 = trim(strtolower(preg_replace('/[^a-z0-9]/', '', $text2)));
        
        return $normalized1 === $normalized2;
    }

    /**
     * Check if amounts match (within 0.01 tolerance)
     */
    private function isAmountMatch($amount1, $amount2)
    {
        if ($amount1 === null || $amount2 === null) {
            return false;
        }
        
        return abs(floatval($amount1) - floatval($amount2)) < 0.01;
    }

    /**
     * Calculate match score between bank and ledger transaction
     */
    private function calculateMatchScore($bankAmount, $bankDate, $bankDescription, $ledgerAmount, $ledgerDate, $ledgerDescription)
    {
        $score = 0;
        
        // Amount matching (50 points max - increased weight)
        $amountDiff = abs($ledgerAmount - $bankAmount);
        if ($amountDiff < 0.01) {
            $score += 50;
        } elseif ($amountDiff <= max(0.01, $bankAmount * 0.01)) {
            $score += 40;
        } elseif ($amountDiff <= max(0.01, $bankAmount * 0.05)) {
            $score += 30;
        } elseif ($amountDiff <= max(0.01, $bankAmount * 0.10)) {
            $score += 20;
        } elseif ($amountDiff <= max(0.01, $bankAmount * 0.20)) {
            $score += 10;
        }
        
        // Date matching (30 points max)
        if (!empty($bankDate) && !empty($ledgerDate)) {
            $bankTimestamp = strtotime($bankDate);
            $ledgerTimestamp = strtotime($ledgerDate);
            if ($bankTimestamp && $ledgerTimestamp) {
                $dateDiff = abs($bankTimestamp - $ledgerTimestamp) / (60 * 60 * 24);
                if ($dateDiff == 0) {
                    $score += 30;
                } elseif ($dateDiff <= 3) {
                    $score += 20;
                } elseif ($dateDiff <= 7) {
                    $score += 10;
                } elseif ($dateDiff <= 14) {
                    $score += 5;
                }
            }
        }
        
        // Description matching (20 points max)
        if (!empty($bankDescription) && !empty($ledgerDescription)) {
            similar_text($bankDescription, $ledgerDescription, $percent);
            
            if ($percent > 80) {
                $score += 20;
            } elseif ($percent > 60) {
                $score += 15;
            } elseif ($percent > 40) {
                $score += 10;
            } elseif ($percent > 20) {
                $score += 5;
            }
        }
        
        return min(100, $score);
    }

    /**
     * Get match type based on score
     */
    private function getMatchType($score)
    {
        if ($score >= 90) return 'exact';
        if ($score >= 75) return 'high';
        if ($score >= 60) return 'medium';
        return 'low';
    }
    
    /**
     * Reconcile statement - mark as reconciled
     */
    public function reconcile(Request $request, $id)
    {
        $submission = BankStatementSubmission::where('id', $id)
            ->where('created_by', Auth::user()->creatorId())
            ->firstOrFail();
        
        $reconciledTransactionIds = $request->input('reconciled_transaction_ids', []);
        
        // Update reconciled transactions
        $transactions = $submission->transactions ?? [];
        foreach ($transactions as $index => $transaction) {
            $transactions[$index]['reconciled'] = in_array($index, $reconciledTransactionIds);
        }
        
        $submission->update([
            'reconciliation_status' => count($reconciledTransactionIds) >= count($transactions) ? 'completed' : 'partial',
            'reconciled_at' => now(),
            'reconciled_transactions' => $reconciledTransactionIds,
            'transactions' => $transactions
        ]);
        
        return redirect()
            ->route('bank-statement.show', $submission->id)
            ->with('success', __('Statement reconciled successfully.'));
    }
    
    /**
     * Export comparison report
     */
    public function exportComparison($id, Request $request)
    {
        $submission = BankStatementSubmission::where('id', $id)
            ->where('created_by', Auth::user()->creatorId())
            ->firstOrFail();
            
        $ledgerId = $request->input('ledger_id');
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-t'));
        
        $ledgerTransactions = $this->getLedgerTransactions($ledgerId, $startDate, $endDate);
        $bankTransactions = $submission->transactions ?? [];
        $comparison = $this->compareTransactions($bankTransactions, $ledgerTransactions);
        
        $filename = 'reconciliation_' . $submission->id . '_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($comparison, $submission) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, ['Bank Statement Reconciliation Report']);
            fputcsv($file, ['Statement ID:', $submission->id]);
            fputcsv($file, ['Bank Name:', $submission->bank_name ?? 'N/A']);
            fputcsv($file, ['Account Number:', $submission->account_number ?? 'N/A']);
            fputcsv($file, ['Generated:', now()->format('Y-m-d H:i:s')]);
            fputcsv($file, []);
            
            // Summary
            fputcsv($file, ['SUMMARY']);
            fputcsv($file, ['Total Bank Transactions:', $comparison['total_bank']]);
            fputcsv($file, ['Total Ledger Transactions:', $comparison['total_ledger']]);
            fputcsv($file, ['Matched Transactions:', $comparison['total_matched']]);
            fputcsv($file, ['Match Rate:', $comparison['match_rate'] . '%']);
            fputcsv($file, ['Total Bank Amount:', number_format($comparison['total_bank_amount'], 2)]);
            fputcsv($file, ['Total Ledger Amount:', number_format($comparison['total_ledger_amount'], 2)]);
            fputcsv($file, ['Difference:', number_format($comparison['difference'], 2)]);
            fputcsv($file, []);
            
            // Field-level comparison
            fputcsv($file, ['DETAILED COMPARISON']);
            fputcsv($file, ['Status', 'PDF Date', 'PDF Description', 'PDF Amount', 'PDF Reference', 'Ledger Date', 'Ledger Description', 'Ledger Amount', 'Ledger Reference', 'Match Score']);
            
            foreach ($comparison['results'] as $result) {
                fputcsv($file, [
                    $result->status,
                    $result->pdf_date ?? 'N/A',
                    $result->pdf_account ?? 'N/A',
                    isset($result->pdf_amount) ? number_format($result->pdf_amount, 2) : 'N/A',
                    $result->pdf_reference ?? 'N/A',
                    $result->ledger_date ?? 'N/A',
                    $result->ledger_account ?? 'N/A',
                    isset($result->ledger_amount) ? number_format($result->ledger_amount, 2) : 'N/A',
                    $result->ledger_reference ?? 'N/A',
                    $result->match_score . '%'
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}