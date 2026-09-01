<?php
// app/Services/BankStatementExtractionService.php

namespace App\Services;

use thiagoalessio\TesseractOCR\TesseractOCR;
use Smalot\PdfParser\Parser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BankStatementExtractionService
{
    private $tesseractPath;
    private $popplerPath;
    
    public function __construct()
    {
        $this->tesseractPath = config('services.tesseract.path', '');
        $this->popplerPath = config('services.poppler.path', '');
    }
    
    /**
     * Extract text from uploaded file
     */
    public function extractText($filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
            return $this->extractFromImage($filePath);
        }
        
        if ($extension === 'pdf') {
            return $this->extractFromPdf($filePath);
        }
        
        throw new \Exception("Unsupported file format: {$extension}");
    }
    
    /**
     * Extract text from image using Tesseract OCR
     */
    private function extractFromImage($filePath): string
    {
        try {
            // ✅ WINDOWS FIX: Auto-detect Tesseract path
            $possiblePaths = [
                'C:\\Program Files\\Tesseract-OCR\\tesseract.exe',
                'C:\\Program Files (x86)\\Tesseract-OCR\\tesseract.exe',
                'tesseract',
            ];
            
            $tesseractPath = $this->tesseractPath;
            foreach ($possiblePaths as $path) {
                if (file_exists($path)) {
                    $tesseractPath = $path;
                    break;
                }
            }
            
            $tesseract = new TesseractOCR($filePath);
            
            if ($tesseractPath && $tesseractPath !== 'tesseract') {
                $tesseract->executable($tesseractPath);
            }
            
            $tesseract->lang('eng+equ');
            $tesseract->dpi(300);
            $tesseract->psm(6);
            $tesseract->oem(3); // Default OCR Engine
            
            $text = $tesseract->run();
            
            \Log::info('OCR raw output length: ' . strlen($text));
            
            if (empty(trim($text))) {
                throw new \Exception("OCR extracted no readable text");
            }
            
            return $this->cleanText($text);
            
        } catch (\Throwable $e) {
            \Log::error("OCR failed: " . $e->getMessage() . " | Path: {$this->tesseractPath}");
            
            // Fallback: Try without custom path
            try {
                $tesseract = new TesseractOCR($filePath);
                $tesseract->lang('eng');
                $text = $tesseract->run();
                if (!empty(trim($text))) {
                    return $this->cleanText($text);
                }
            } catch (\Throwable $fallbackE) {
                // Ignore fallback failure
            }
            
            throw new \Exception("OCR failed completely: " . $e->getMessage());
        }
    }

    
    /**
     * Extract text from PDF (digital or scanned)
     */
    private function extractFromPdf($filePath): string
    {
        // Try digital PDF extraction first
        $digitalText = $this->extractDigitalPdfText($filePath);
        if (strlen(trim($digitalText)) > 100) {
            return $digitalText;
        }
        
        // If digital extraction fails, try OCR on scanned PDF
        return $this->extractScannedPdfText($filePath);
    }
    
    /**
     * Extract text from digital PDF
     */
    private function extractDigitalPdfText($filePath): string
    {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();
            return $this->cleanText($text);
        } catch (\Exception $e) {
            Log::warning("Digital PDF extraction failed: " . $e->getMessage());
            return "";
        }
    }
    
    /**
     * Extract text from scanned PDF using OCR
     */
    private function extractScannedPdfText($filePath): string
    {
        try {
            // Convert PDF to images
            $images = $this->pdfToImages($filePath);
            $allText = [];
            
            foreach ($images as $imagePath) {
                $text = $this->extractFromImage($imagePath);
                $allText[] = $text;
                // Clean up temporary image
                @unlink($imagePath);
            }
            
            return implode("\n", $allText);
            
        } catch (\Exception $e) {
            Log::error("Scanned PDF extraction failed: " . $e->getMessage());
            throw new \Exception("Failed to extract text from scanned PDF: " . $e->getMessage());
        }
    }
    
    /**
     * Convert PDF pages to images (requires imagick or gs)
     */
    private function pdfToImages($pdfPath): array
    {
        $images = [];
        $imagick = new \Imagick();
        $imagick->setResolution(300, 300);
        $imagick->readImage($pdfPath);
        
        foreach ($imagick as $index => $page) {
            $page->setImageFormat('png');
            $tempPath = storage_path("app/temp/page_{$index}.png");
            $page->writeImage($tempPath);
            $images[] = $tempPath;
        }
        
        $imagick->clear();
        return $images;
    }
    
    /**
     * Clean extracted text
     */
    private function cleanText($text): string
    {
        // Remove extra whitespace
        $text = preg_replace('/[ \t]+/', ' ', $text);
        // Remove multiple newlines
        $text = preg_replace("/\n{3,}/", "\n\n", $text);
        // Replace carriage returns
        $text = str_replace("\r", "\n", $text);
        
        return trim($text);
    }
    
    /**
     * Extract structured data from bank statement text
     */
    public function extractStructuredData(string $text): array
    {
        $lines = array_filter(array_map('trim', explode("\n", $text)));
        
        return [
            'account_name' => $this->extractAccountName($lines),
            'account_number' => $this->extractAccountNumber($text, $lines),
            'ifsc_code' => $this->extractIfscCode($text, $lines),
            'bank_name' => $this->extractBankName($lines),
            'branch' => $this->extractBranch($lines),
            'transactions' => $this->extractTransactions($lines),
            'confidence' => $this->calculateConfidence($lines)
        ];
    }
    
    /**
     * Extract account name using label matching
     */
    private function extractAccountName(array $lines): ?string
    {
        $patterns = ['/account name/i', '/a\/c name/i', '/name of account/i', '/account holder/i'];
        
        foreach ($lines as $index => $line) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $line)) {
                    // Get value after colon or at next line
                    if (preg_match('/[:\-]\s*(.+)$/', $line, $matches)) {
                        return $this->normalizeText($matches[1]);
                    }
                    if (isset($lines[$index + 1])) {
                        return $this->normalizeText($lines[$index + 1]);
                    }
                }
            }
        }
        
        return null;
    }
    
    /**
     * Extract account number
     */
    private function extractAccountNumber(string $text, array $lines): ?string
    {
        // Try labeled extraction first
        $patterns = ['/account number/i', '/account no/i', '/a\/c no/i', '/acc no/i'];
        
        foreach ($lines as $index => $line) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $line)) {
                    if (preg_match('/[:\-]\s*(\d{9,18})/', $line, $matches)) {
                        return $matches[1];
                    }
                    if (isset($lines[$index + 1]) && preg_match('/\b(\d{9,18})\b/', $lines[$index + 1], $matches)) {
                        return $matches[1];
                    }
                }
            }
        }
        
        // Fallback: find any 9-18 digit number
        if (preg_match('/\b(\d{9,18})\b/', $text, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    /**
     * Extract IFSC code
     */
    private function extractIfscCode(string $text, array $lines): ?string
    {
        // Direct IFSC pattern
        if (preg_match('/\b([A-Z]{4}0[A-Z0-9]{6})\b/i', $text, $matches)) {
            return strtoupper($matches[1]);
        }
        
        // Labeled extraction
        foreach ($lines as $line) {
            if (preg_match('/ifsc/i', $line)) {
                if (preg_match('/[:\-]\s*([A-Z0-9]{11})/i', $line, $matches)) {
                    return strtoupper($matches[1]);
                }
            }
        }
        
        return null;
    }
    
    /**
     * Extract bank name
     */
    private function extractBankName(array $lines): ?string
    {
        foreach ($lines as $line) {
            if (preg_match('/bank name/i', $line)) {
                if (preg_match('/[:\-]\s*(.+)$/', $line, $matches)) {
                    return $this->normalizeText($matches[1]);
                }
            }
        }
        
        // Look for common bank names
        $commonBanks = ['SBI', 'HDFC', 'ICICI', 'AXIS', 'PNB', 'CANARA', 'UNION', 'BANK OF'];
        foreach ($lines as $line) {
            foreach ($commonBanks as $bank) {
                if (stripos($line, $bank) !== false && strlen($line) < 50) {
                    return $this->normalizeText($line);
                }
            }
        }
        
        return null;
    }
    
    /**
     * Extract branch
     */
    private function extractBranch(array $lines): ?string
    {
        foreach ($lines as $line) {
            if (preg_match('/branch/i', $line)) {
                if (preg_match('/[:\-]\s*(.+)$/', $line, $matches)) {
                    return $this->normalizeText($matches[1]);
                }
            }
        }
        
        return null;
    }
    
    /**
     * Extract transactions from statement lines
     */
    private function extractTransactions(array $lines): array
    {
        $transactions = [];
        $serialNumber = 1;
        
        foreach ($lines as $line) {
            $normalized = $this->normalizeText($line);
            if (empty($normalized)) {
                continue;
            }
            
            // Look for date pattern
            if (!preg_match('/\b(\d{2}[-/]\d{2}[-/]\d{2,4}|\d{4}[-/]\d{2}[-/]\d{2})\b/', $normalized, $dateMatch)) {
                continue;
            }
            
            $date = $dateMatch[1];
            
            // Look for amount patterns
            preg_match_all('/[-+]?[\d,]+\.?\d{0,2}/', $normalized, $amountMatches);
            $amounts = $amountMatches[0];
            
            if (count($amounts) < 2) {
                continue;
            }
            
            // Extract description (text before the date)
            $datePos = strpos($normalized, $date);
            $description = trim(substr($normalized, 0, $datePos));
            
            // Determine transaction type
            $type = 'Transaction';
            if (preg_match('/(NEFT|RTGS|IMPS|UPI|CHEQUE|TRANSFER)/i', $description, $typeMatch)) {
                $type = strtoupper($typeMatch[1]);
            }
            
            // Extract debit, credit, balance
            $debit = null;
            $credit = null;
            $balance = end($amounts);
            
            if (count($amounts) >= 2) {
                $amount1 = $this->normalizeAmount($amounts[0]);
                $amount2 = $this->normalizeAmount($amounts[1]);
                
                // Determine which is debit/credit based on context
                if (stripos($description, 'dr') !== false || stripos($description, 'debit') !== false) {
                    $debit = $amount1;
                    $credit = $amount2;
                } else {
                    $credit = $amount1;
                    $debit = $amount2;
                }
            }
            
            $transactions[] = [
                'serial_number' => $serialNumber++,
                'date' => $this->standardizeDate($date),
                'description' => $description,
                'type' => $type,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $this->normalizeAmount($balance),
            ];
        }
        
        return $transactions;
    }
    
    /**
     * Normalize text
     */
    private function normalizeText($text): string
    {
        $text = preg_replace('/[|_]/', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }
    
    /**
     * Normalize amount
     */
    private function normalizeAmount($amount): ?string
    {
        if (!$amount || $amount == '-' || $amount == '+') {
            return null;
        }
        $amount = str_replace(',', '', $amount);
        return number_format((float) $amount, 2, '.', '');
    }
    
    /**
     * Standardize date format to Y-m-d
     */
    private function standardizeDate($date): string
    {
        // Handle DD-MM-YYYY or DD/MM/YYYY
        if (preg_match('/(\d{2})[-/](\d{2})[-/](\d{4})/', $date, $matches)) {
            return "{$matches[3]}-{$matches[2]}-{$matches[1]}";
        }
        // Handle YYYY-MM-DD
        if (preg_match('/(\d{4})[-/](\d{2})[-/](\d{2})/', $date, $matches)) {
            return "{$matches[1]}-{$matches[2]}-{$matches[3]}";
        }
        return $date;
    }
    
    /**
     * Calculate confidence scores
     */
    private function calculateConfidence(array $lines): array
    {
        $transactionCount = count($this->extractTransactions($lines));
        
        return [
            'account_name' => 0.7,
            'account_number' => 0.6,
            'ifsc_code' => 0.8,
            'bank_name' => 0.65,
            'branch' => 0.6,
            'transactions' => min(1.0, 0.5 + ($transactionCount * 0.05))
        ];
    }
}