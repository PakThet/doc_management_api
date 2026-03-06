<?php
// app/Imports/BranchesImport.php

namespace App\Imports;

use App\Models\Branch;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Validators\Failure;

class BranchesImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure, WithChunkReading
{
    use Importable;

    protected $organizationId;
    protected $updateExisting;
    protected $importedCount = 0;
    protected $updatedCount = 0;
    protected $failedCount = 0;
    protected $errors = [];

    public function __construct($organizationId, $updateExisting = false)
    {
        $this->organizationId = $organizationId;
        $this->updateExisting = $updateExisting;
    }

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                // Check if branch exists
                $branch = Branch::where('organization_id', $this->organizationId)
                    ->where(function ($query) use ($row) {
                        $query->where('code', $row['code'])
                              ->orWhere('name', $row['name'])
                              ->orWhere('email', $row['email'] ?? null);
                    })
                    ->first();

                $data = [
                    'organization_id' => $this->organizationId,
                    'name' => $row['name'],
                    'code' => $row['code'],
                    'address' => $row['address'] ?? null,
                    'city' => $row['city'] ?? null,
                    'state' => $row['state'] ?? null,
                    'country' => $row['country'] ?? null,
                    'postal_code' => $row['postal_code'] ?? null,
                    'phone' => $row['phone'] ?? null,
                    'email' => $row['email'] ?? null,
                    'status' => $row['status'] ?? 'active',
                ];

                if ($branch && $this->updateExisting) {
                    $branch->update($data);
                    $this->updatedCount++;
                } elseif (!$branch) {
                    Branch::create($data);
                    $this->importedCount++;
                } else {
                    $this->failedCount++;
                    $this->errors[] = "Row {$row['code']}: Branch already exists and update_existing is false";
                }

            } catch (\Exception $e) {
                $this->failedCount++;
                $this->errors[] = "Row {$row['code']}: " . $e->getMessage();
            }
        }
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'nullable|in:active,inactive',
        ];
    }

    /**
     * Get the row count
     */
    public function getRowCount(): int
    {
        return $this->importedCount + $this->updatedCount + $this->failedCount;
    }

    /**
     * Get imported count
     */
    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    /**
     * Get updated count
     */
    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
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