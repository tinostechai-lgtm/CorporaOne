<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    private array $allowedExtensions = ['pdf', 'png', 'jpg', 'jpeg'];
    private int $maxFileSize = 10485760; // 10MB

    public function validate(UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw new \Exception("File upload failed.");
        }

        $extension = strtolower($file->getClientOriginalExtension());
        
        if (!in_array($extension, $this->allowedExtensions)) {
            throw new \Exception("Invalid file type. Allowed: PDF, PNG, JPG, JPEG.");
        }

        if ($file->getSize() > $this->maxFileSize) {
            throw new \Exception("File size exceeds maximum allowed size of 10MB.");
        }
    }

    public function upload(UploadedFile $file, string $directory = 'bank-statements'): array
    {
        $this->validate($file);
        
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $storedName = Str::uuid() . '.' . $extension;
        
        $path = $file->storeAs($directory, $storedName, 'public');
        
        return [
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'path' => Storage::disk('public')->path($path),
            'extension' => $extension,
        ];
    }
}