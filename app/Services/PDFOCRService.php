<?php

namespace App\Services;

use Smalot\PdfParser\Parser;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Spatie\PdfToImage\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PDFOCRService
{
    protected $tesseractPath;
    protected $popplerPath;
    
    public function __construct()
    {
        // Set paths for Windows
        $this->tesseractPath = 'C:\Program Files\Tesseract-OCR\tesseract.exe';
        $this->popplerPath = 'C:\poppler\bin'; // You'll need to install poppler
        
        // For Linux/Mac:
        // $this->tesseractPath = '/usr/bin/tesseract';
        // $this->popplerPath = '/usr/bin';
    }
    
    /**
     * Main method to extract text from PDF or Image
     */
    public function extractText(string $filePath, string $fileType = 'pdf'): string
    {
        try {
            if ($fileType === 'pdf') {
                return $this->extractFromPDF($filePath);
            } elseif (in_array($fileType, ['png', 'jpg', 'jpeg'])) {
                return $this->extractFromImage($filePath);
            }
            
            throw new \Exception('Unsupported file format: ' . $fileType);
            
        } catch (\Exception $e) {
            Log::error('OCR Extraction failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Extract text from PDF (digital or scanned)
     */
    protected function extractFromPDF(string $pdfPath): string
    {
        // First try digital PDF extraction
        $digitalText = $this->extractDigitalPDFText($pdfPath);
        
        if (!empty(trim($digitalText))) {
            Log::info('Digital PDF text extracted successfully', [
                'length' => strlen($digitalText)
            ]);
            return $digitalText;
        }
        
        // If no text found, try OCR for scanned PDF
        Log::info('No digital text found, trying OCR for scanned PDF');
        return $this->extractScannedPDFText($pdfPath);
    }
    
    /**
     * Extract text from digital PDF (selectable text)
     */
    protected function extractDigitalPDFText(string $pdfPath): string
    {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($pdfPath);
            $text = $pdf->getText();
            
            return $text ?? '';
            
        } catch (\Exception $e) {
            Log::warning('Digital PDF extraction failed: ' . $e->getMessage());
            return '';
        }
    }
    
    /**
     * Extract text from scanned PDF using OCR
     */
    protected function extractScannedPDFText(string $pdfPath): string
    {
        try {
            // Convert PDF pages to images
            $images = $this->pdfToImages($pdfPath);
            
            $fullText = '';
            foreach ($images as $index => $imagePath) {
                $pageText = $this->extractFromImage($imagePath);
                $fullText .= "--- Page " . ($index + 1) . " ---\n" . $pageText . "\n\n";
                
                // Clean up temp image
                if (file_exists($imagePath)) {
                    @unlink($imagePath);
                }
            }
            
            return $fullText;
            
        } catch (\Exception $e) {
            Log::error('Scanned PDF OCR failed: ' . $e->getMessage());
            throw new \Exception('OCR processing failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Extract text from image using Tesseract OCR
     */
    protected function extractFromImage(string $imagePath): string
    {
        try {
            // Prepare image for better OCR results
            $preparedImage = $this->prepareImageForOCR($imagePath);
            
            // Run Tesseract OCR
            $ocr = new TesseractOCR($preparedImage);
            $ocr->lang('eng');
            $ocr->config('--oem 3 --psm 6');
            
            if (!empty($this->tesseractPath) && file_exists($this->tesseractPath)) {
                $ocr->executable($this->tesseractPath);
            }
            
            $text = $ocr->run();
            
            // Clean up temp prepared image
            if ($preparedImage !== $imagePath && file_exists($preparedImage)) {
                @unlink($preparedImage);
            }
            
            return $text ?? '';
            
        } catch (\Exception $e) {
            Log::error('Image OCR failed: ' . $e->getMessage());
            return '';
        }
    }
    
    /**
     * Prepare image for better OCR accuracy
     */
    protected function prepareImageForOCR(string $imagePath): string
    {
        if (!extension_loaded('imagick')) {
            Log::warning('Imagick extension not loaded, using original image');
            return $imagePath;
        }
        
        try {
            $imagick = new \Imagick($imagePath);
            
            // Convert to grayscale
            $imagick->setImageColorspace(\Imagick::COLORSPACE_GRAY);
            
            // Increase contrast
            $imagick->contrastImage(1);
            
            // Sharpen the image
            $imagick->sharpenImage(1, 1);
            
            // Apply median filter for noise reduction
            $imagick->medianFilterImage(3);
            
            // Set resolution to 300 DPI for better OCR
            $imagick->setImageResolution(300, 300);
            
            // Save prepared image
            $tempPath = storage_path('app/temp/prepared_' . time() . '_' . uniqid() . '.png');
            $imagick->writeImage($tempPath);
            $imagick->clear();
            
            return $tempPath;
            
        } catch (\Exception $e) {
            Log::warning('Image preparation failed: ' . $e->getMessage());
            return $imagePath;
        }
    }
    
    /**
     * Convert PDF pages to images
     */
    protected function pdfToImages(string $pdfPath, int $dpi = 300): array
    {
        $images = [];
        
        try {
            // Create temp directory if not exists
            $tempDir = storage_path('app/temp');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0777, true);
            }
            
            // Use Imagick to convert PDF to images
            if (extension_loaded('imagick')) {
                $imagick = new \Imagick();
                $imagick->setResolution($dpi, $dpi);
                $imagick->readImage($pdfPath);
                
                foreach ($imagick as $key => $page) {
                    $page->setImageFormat('png');
                    $tempPath = $tempDir . '/page_' . $key . '_' . uniqid() . '.png';
                    $page->writeImage($tempPath);
                    $images[] = $tempPath;
                }
                
                $imagick->clear();
            } else {
                // Fallback to Spatie PDF to Image
                $pdf = new Pdf($pdfPath);
                $pdf->setResolution($dpi);
                $pdf->setOutputFormat('png');
                
                $tempFiles = $pdf->saveAllPages($tempDir, 'page');
                $images = $tempFiles;
            }
            
            return $images;
            
        } catch (\Exception $e) {
            Log::error('PDF to images conversion failed: ' . $e->getMessage());
            throw new \Exception('Failed to convert PDF to images: ' . $e->getMessage());
        }
    }
    
    /**
     * Parse OCR text to extract bank transactions
     */
    public function parseTransactionsFromText(string $text): array
    {
        $transactions = [];
        $lines = explode("\n", $text);
        
        // Patterns for matching transactions
        $patterns = [
            '/(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4})\s+(.+?)\s+([\d,]+\.\d{2})/i',
            '/(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4})\s+([\d,]+\.\d{2})\s+(.+)/i',
            '/(\d{4}-\d{2}-\d{2})\s+(.+?)\s+([\d,]+\.\d{2})/i',
        ];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (strlen($line) < 20) continue;
            
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $line, $matches)) {
                    $date = $this->normalizeDate($matches[1]);
                    $amount = floatval(str_replace(',', '', $matches[count($matches) - 1]));
                    $description = trim($matches[2]);
                    
                    if ($amount <= 0) continue;
                    
                    // Determine if debit or credit
                    $isNegative = strpos($line, '-') !== false;
                    $isCredit = preg_match('/cr|credit|deposit/i', $line);
                    
                    $transactions[] = [
                        'date' => $date,
                        'description' => substr($description, 0, 100),
                        'debit' => ($isNegative && !$isCredit) ? $amount : 0,
                        'credit' => (!$isNegative || $isCredit) ? $amount : 0,
                        'amount' => $amount,
                        'reference' => ''
                    ];
                    break;
                }
            }
        }
        
        // Remove duplicates
        $unique = [];
        $seen = [];
        foreach ($transactions as $txn) {
            $key = $txn['date'] . '_' . $txn['amount'];
            if (!in_array($key, $seen)) {
                $seen[] = $key;
                $unique[] = $txn;
            }
        }
        
        return $unique;
    }
    
    /**
     * Normalize date to YYYY-MM-DD format
     */
    protected function normalizeDate($date)
    {
        $date = str_replace('/', '-', $date);
        $formats = ['Y-m-d', 'd-m-Y', 'm-d-Y'];
        
        foreach ($formats as $format) {
            $parsed = date_create_from_format($format, $date);
            if ($parsed !== false) {
                return $parsed->format('Y-m-d');
            }
        }
        
        $timestamp = strtotime($date);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }
        
        return date('Y-m-d');
    }
}