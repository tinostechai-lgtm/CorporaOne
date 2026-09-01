<?php
// app/Jobs/ProcessBankStatement.php

namespace App\Jobs;

use App\Models\BankStatementSubmission;
use App\Services\BankStatementExtractionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessBankStatement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $submissionId;
    
    public function __construct($submissionId)
    {
        $this->submissionId = $submissionId;
    }
    
    public function handle(BankStatementExtractionService $extractionService)
    {
        \Log::info('Processing bank statement job', ['submission_id' => $this->submissionId]);
        
        try {
            $submission = BankStatementSubmission::find($this->submissionId);
            if (!$submission) {
                \Log::warning('Submission not found', ['id' => $this->submissionId]);
                return;
            }
            
            // Check if already processed
            if ($submission->status === 'completed') {
                \Log::info('Already processed', ['id' => $submission->id]);
                return;
            }
            
            $fullPath = Storage::disk('public')->path($submission->file_path);
            if (!file_exists($fullPath)) {
                throw new \Exception("File not found: {$fullPath}");
            }
            
            \Log::info('Extracting text', ['file' => $fullPath]);
            $text = $extractionService->extractText($fullPath);
            
            if (empty(trim($text))) {
                throw new \Exception('No text extracted from PDF');
            }
            
            \Log::info('Text extracted successfully, length: ' . strlen($text));
            
            $data = $extractionService->extractStructuredData($text);
            $transactionCount = count($data['transactions']);
            
            \Log::info('Structured data extracted', [
                'transactions_count' => $transactionCount,
                'account_name' => $data['account_name'] ?? 'N/A',
                'bank_name' => $data['bank_name'] ?? 'N/A'
            ]);
            
            $submission->update([
                'account_name' => $data['account_name'],
                'account_number' => $data['account_number'],
                'ifsc_code' => $data['ifsc_code'],
                'bank_name' => $data['bank_name'],
                'branch' => $data['branch'],
                'transactions' => $data['transactions'],
                'extraction_confidence' => json_encode($data['confidence']),
                'status' => $transactionCount > 0 ? 'completed' : 'partial',
                'transaction_count' => $transactionCount
            ]);
            
            \Log::info('✅ Extraction completed successfully', [
                'id' => $submission->id,
                'transactions' => $transactionCount
            ]);
            
        } catch (\Throwable $e) {  // Catch all errors
            \Log::error('❌ Bank statement extraction FAILED', [
                'submission_id' => $this->submissionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            BankStatementSubmission::where('id', $this->submissionId)->update([
                'status' => 'failed',
                'extraction_error' => $e->getMessage(),
                'extraction_confidence' => json_encode(['error' => $e->getMessage()])
            ]);
        }
    }

}