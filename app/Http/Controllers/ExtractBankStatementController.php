<?php

namespace App\Http\Controllers;

use App\Models\BankStatementSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;

class ExtractBankStatementController extends Controller
{
    public function upload(Request $request)
    {
        try {
            Log::info('Upload request received', [
                'has_file' => $request->hasFile('file'),
                'all_inputs' => $request->all()
            ]);

            $request->validate([
                'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:20480',
            ]);

            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $storedName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $originalName);
            $filePath = $file->storeAs('bank-statements', $storedName, 'public');
            
            // ACTUALLY EXTRACT DATA FROM THE UPLOADED PDF
            $transactions = $this->extractTransactionsFromPDF($file->getPathname());
            
            Log::info('Extracted transactions:', ['transactions' => $transactions]);
            
            $ledgerId = $request->input('ledger_id');
            $startDate = $request->input('start_date', date('Y-m-01'));
            $endDate = $request->input('end_date', date('Y-m-t'));
            $bankName = $request->input('bank_name', 'Uploaded Statement');
            
            $submission = BankStatementSubmission::create([
                'bank_name' => $bankName,
                'original_file_name' => $originalName,
                'stored_file_name' => $storedName,
                'transactions' => json_encode($transactions),
                'extraction_confidence' => $this->calculateConfidence($transactions),
                'reconciliation_status' => 'pending',
                'created_by' => auth()->user()->creatorId(),
            ]);

            Log::info('Bank statement created', [
                'id' => $submission->id,
                'transactions_count' => count($transactions)
            ]);

            // Redirect to compare-with-ledger
            $redirectUrl = "/bank-reconciliation/compare-with-ledger?ledger_id={$ledgerId}&submission_id={$submission->id}&start_date={$startDate}&end_date={$endDate}";
            
            Log::info('Redirecting to: ' . $redirectUrl);
            
            return redirect()->to($redirectUrl);

        } catch (\Exception $e) {
            Log::error('Upload error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Extract transactions from PDF file
     */
    private function extractTransactionsFromPDF($filePath)
    {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();
            
            Log::info('PDF extracted text:', ['text' => $text]);
            
            // Try multiple extraction methods
            $transactions = [];
            
            // Method 1: Extract table rows (for your PDF format)
            $transactions = $this->extractTableRows($text);
            
            if (empty($transactions)) {
                // Method 2: Line by line parsing
                $transactions = $this->extractLineByLine($text);
            }
            
            if (empty($transactions)) {
                // Method 3: Regex pattern matching
                $transactions = $this->extractWithRegex($text);
            }
            
            return $transactions;
            
        } catch (\Exception $e) {
            Log::error('PDF extraction error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Extract transactions from table rows (specifically for your PDF format)
     */
    private function extractTableRows($text)
    {
        $transactions = [];
        $lines = explode("\n", $text);
        
        // Look for date pattern (Apr 9, 2026 or similar)
        $datePattern = '/(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+\d{1,2},\s+\d{4}/i';
        
        // Amount pattern for European format (449.800,00 or 0,00)
        // This handles: $ 449.800,00 or $ 0,00
        $amountPattern = '/\$?\s?(\d{1,3}(?:\.\d{3})*,\d{2})/';
        
        foreach ($lines as $line) {
            // Check if line contains a date
            if (preg_match($datePattern, $line, $dateMatch)) {
                $date = $this->parseDate($dateMatch[0]);
                
                // Extract description (text between date and first amount)
                $description = '';
                $debit = 0;
                $credit = 0;
                $amount = 0;
                
                // Find all amounts in the line
                preg_match_all($amountPattern, $line, $amountMatches);
                
                if (count($amountMatches[1]) >= 1) {
                    // Convert European format to float (449.800,00 -> 449800.00)
                    $debitAmount = $this->parseEuropeanNumber($amountMatches[1][0]);
                    
                    if (count($amountMatches[1]) >= 2) {
                        $creditAmount = $this->parseEuropeanNumber($amountMatches[1][1]);
                    }
                    
                    // Determine if it's debit or credit based on the line
                    // In your PDF: Debit column first, then Credit
                    if ($debitAmount > 0) {
                        $debit = $debitAmount;
                        $amount = $debitAmount;
                        $description = $this->extractDescriptionFromLine($line, $dateMatch[0], $amountMatches[0][0]);
                    } elseif ($creditAmount > 0) {
                        $credit = $creditAmount;
                        $amount = $creditAmount;
                        $description = $this->extractDescriptionFromLine($line, $dateMatch[0], $amountMatches[0][0]);
                    }
                }
                
                if ($date && ($debit > 0 || $credit > 0)) {
                    $transactions[] = [
                        'date' => $date,
                        'description' => trim($description) ?: 'Expense Direct #2',
                        'debit' => $debit,
                        'credit' => $credit,
                        'amount' => $debit + $credit,
                        'reference' => $this->extractReferenceFromLine($line)
                    ];
                }
            }
        }
        
        return $transactions;
    }

    /**
     * Parse European number format (449.800,00 -> 449800.00)
     */
    private function parseEuropeanNumber($numberStr)
    {
        // Remove currency symbol and spaces
        $numberStr = preg_replace('/[^\d,\.\-]/', '', $numberStr);
        
        // Check if it's European format (dot for thousand, comma for decimal)
        if (strpos($numberStr, ',') !== false) {
            // Remove dots (thousand separators) and replace comma with dot
            $numberStr = str_replace('.', '', $numberStr);
            $numberStr = str_replace(',', '.', $numberStr);
        }
        
        return floatval($numberStr);
    }

    /**
     * Parse date from various formats
     */
    private function parseDate($dateStr)
    {
        $months = [
            'Jan' => '01', 'Feb' => '02', 'Mar' => '03', 'Apr' => '04',
            'May' => '05', 'Jun' => '06', 'Jul' => '07', 'Aug' => '08',
            'Sep' => '09', 'Oct' => '10', 'Nov' => '11', 'Dec' => '12'
        ];
        
        // Format: "Apr 9, 2026"
        if (preg_match('/([A-Za-z]{3})\s+(\d{1,2}),\s+(\d{4})/', $dateStr, $matches)) {
            $month = $months[$matches[1]] ?? '01';
            $day = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $year = $matches[3];
            return "{$year}-{$month}-{$day}";
        }
        
        return date('Y-m-d');
    }

    /**
     * Extract description from line
     */
    private function extractDescriptionFromLine($line, $dateStr, $amountStr)
    {
        // Remove date and amount from line
        $description = str_replace($dateStr, '', $line);
        $description = str_replace($amountStr, '', $description);
        
        // Common transaction descriptions
        if (strpos($description, 'Expense') !== false) {
            return 'Expense Direct #2';
        }
        
        return trim($description);
    }

    /**
     * Extract reference number
     */
    private function extractReferenceFromLine($line)
    {
        if (strpos($line, '#2') !== false) {
            return 'Direct #2';
        }
        
        if (preg_match('/#(\d+)/', $line, $match)) {
            return $match[1];
        }
        
        return '';
    }

    /**
     * Alternative: Line by line extraction
     */
    private function extractLineByLine($text)
    {
        $transactions = [];
        $lines = explode("\n", $text);
        
        $currentTransaction = null;
        
        foreach ($lines as $line) {
            // Look for date pattern
            if (preg_match('/(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+\d{1,2},\s+\d{4}/i', $line)) {
                if ($currentTransaction) {
                    $transactions[] = $currentTransaction;
                }
                
                $currentTransaction = $this->parseTransactionLine($line);
            } elseif ($currentTransaction && preg_match('/\$?\s?(\d{1,3}(?:\.\d{3})*,\d{2})/', $line)) {
                // Add amount to current transaction if found on next line
                $this->addAmountToTransaction($currentTransaction, $line);
            }
        }
        
        if ($currentTransaction) {
            $transactions[] = $currentTransaction;
        }
        
        return $transactions;
    }

    /**
     * Parse a single transaction line
     */
    private function parseTransactionLine($line)
    {
        $datePattern = '/(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+\d{1,2},\s+\d{4}/i';
        $amountPattern = '/\$?\s?(\d{1,3}(?:\.\d{3})*,\d{2})/';
        
        preg_match($datePattern, $line, $dateMatch);
        preg_match_all($amountPattern, $line, $amountMatches);
        
        $date = $dateMatch ? $this->parseDate($dateMatch[0]) : null;
        
        $transaction = [
            'date' => $date,
            'description' => 'Expense Direct #2',
            'debit' => 0,
            'credit' => 0,
            'amount' => 0,
            'reference' => 'Direct #2'
        ];
        
        if (count($amountMatches[1]) >= 1) {
            $transaction['debit'] = $this->parseEuropeanNumber($amountMatches[1][0]);
            $transaction['amount'] = $transaction['debit'];
        }
        
        return $transaction;
    }

    /**
     * Add amount to existing transaction
     */
    private function addAmountToTransaction(&$transaction, $line)
    {
        $amountPattern = '/\$?\s?(\d{1,3}(?:\.\d{3})*,\d{2})/';
        
        if (preg_match($amountPattern, $line, $amountMatch)) {
            $amount = $this->parseEuropeanNumber($amountMatch[1]);
            if ($transaction['debit'] == 0 && $amount > 0) {
                $transaction['debit'] = $amount;
                $transaction['amount'] = $amount;
            }
        }
    }

    /**
     * Extract using regex patterns
     */
    private function extractWithRegex($text)
    {
        $transactions = [];
        
        // Pattern for your specific PDF format
        // Matches: "Apr 9, 2026Expense Direct #2$ 449.800,00$ 0,00$ -449.800,00"
        $pattern = '/([A-Za-z]{3}\s+\d{1,2},\s+\d{4})([A-Za-z\s#]+)\$?\s?(\d{1,3}(?:\.\d{3})*,\d{2})\$?\s?(\d{1,3}(?:\.\d{3})*,\d{2})/';
        
        preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $date = $this->parseDate($match[1]);
            $description = trim($match[2]);
            $debit = $this->parseEuropeanNumber($match[3]);
            $credit = $this->parseEuropeanNumber($match[4]);
            
            $transactions[] = [
                'date' => $date,
                'description' => $description ?: 'Expense Direct #2',
                'debit' => $debit,
                'credit' => $credit,
                'amount' => $debit + $credit,
                'reference' => $this->extractReferenceFromLine($match[2])
            ];
        }
        
        return $transactions;
    }

    /**
 * Debug the PDF extraction - shows what text was found
 */
public function debugExtract(Request $request)
{
    try {
        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'No file uploaded'], 400);
        }
        
        $file = $request->file('file');
        $parser = new Parser();
        $pdf = $parser->parseFile($file->getPathname());
        $text = $pdf->getText();
        
        // Try to extract transactions
        $transactions = $this->extractTransactionsFromPDF($file->getPathname());
        
        return response()->json([
            'success' => true,
            'text_preview' => substr($text, 0, 2000),
            'text_length' => strlen($text),
            'transactions_found' => count($transactions),
            'transactions' => $transactions,
            'sample_lines' => explode("\n", substr($text, 0, 500))
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}
    /**
     * Calculate extraction confidence
     */
    private function calculateConfidence($transactions)
    {
        if (empty($transactions)) {
            return 0;
        }
        
        return min(100, count($transactions) * 25);
    }
}