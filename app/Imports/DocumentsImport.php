<?php
// app/Imports/DocumentsImport.php

namespace App\Imports;

use App\Models\Document;
use App\Models\DocumentCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Facades\File;

class DocumentsImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure, WithChunkReading
{
    use Importable;

    protected $organizationId;
    protected $userId;
    protected $importedCount = 0;
    protected $failedCount = 0;
    protected $errors = [];

    public function __construct($organizationId, $userId)
    {
        $this->organizationId = $organizationId;
        $this->userId = $userId;
    }

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                // Find or create category
                $category = null;
                if (isset($row['category_name'])) {
                    $category = DocumentCategory::firstOrCreate(
                        [
                            'organization_id' => $this->organizationId,
                            'name' => $row['category_name']
                        ],
                        [
                            'slug' => Str::slug($row['category_name']),
                            'status' => 'active'
                        ]
                    );
                }

                // Generate document number
                $documentNumber = $this->generateDocumentNumber();

                // Generate unique QR token
                $qrToken = Str::random(32);
                while (Document::where('qr_token', $qrToken)->exists()) {
                    $qrToken = Str::random(32);
                }

                // Handle file if provided
                $filePath = null;
                $fileData = null;
                $disk = Storage::disk('public');
                
               if (isset($row['file_path']) && $disk->exists($row['file_path'])) {

    $filePath = $row['file_path'];
    $fullPath = $disk->path($filePath);

    $fileData = [
        'file_name' => basename($filePath),
        'file_type' => pathinfo($filePath, PATHINFO_EXTENSION),
        'file_size' => $disk->size($filePath),
        'mime_type' => File::mimeType($fullPath),
    ];
}

                $data = [
                    'organization_id' => $this->organizationId,
                    'title' => $row['title'],
                    'document_number' => $documentNumber,
                    'qr_token' => $qrToken,
                    'category_id' => $category?->id,
                    'description' => $row['description'] ?? null,
                    'status' => $row['status'] ?? 'draft',
                    'visibility' => $row['visibility'] ?? 'private',
                    'expiry_date' => isset($row['expiry_date']) ? date('Y-m-d', strtotime($row['expiry_date'])) : null,
                    'created_by' => $this->userId,
                    'version' => 1,
                ];

                if ($filePath && $fileData) {
                    $data = array_merge($data, $fileData);
                    $data['file_path'] = $filePath;
                }

                Document::create($data);
                $this->importedCount++;

            } catch (\Exception $e) {
                $this->failedCount++;
                $this->errors[] = "Row {$row['title']}: " . $e->getMessage();
            }
        }
    }

    /**
     * Generate unique document number
     */
    protected function generateDocumentNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $lastDocument = Document::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastDocument) {
            $lastNumber = intval(substr($lastDocument->document_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return 'DOC-' . $year . '-' . $month . '-' . $newNumber;
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'category_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:draft,published,archived',
            'visibility' => 'nullable|in:public,private,restricted',
            'expiry_date' => 'nullable|date',
            'file_path' => 'nullable|string',
        ];
    }

    /**
     * Get the row count
     */
    public function getRowCount(): int
    {
        return $this->importedCount + $this->failedCount;
    }

    /**
     * Get imported count
     */
    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    /**
     * Get failed count
     */
    public function getFailedCount(): int
    {
        return $this->failedCount;
    }

    /**
     * Get errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param \Throwable $e
     */
    public function onError(\Throwable $e)
    {
        $this->errors[] = $e->getMessage();
    }

    /**
     * @param Failure ...$failures
     */
    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errors[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
        }
    }

    /**
     * Chunk size
     */
    public function chunkSize(): int
    {
        return 100;
    }
}