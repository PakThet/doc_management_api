<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $branches = \App\Models\Branch::query()->get();
        $categories = \App\Models\DocumentCategory::query()->get();
        $prefixes = \App\Models\DocumentPrefix::query()->get();
        $creator = User::query()->where('email', 'superadmin@main.com')->first() ?? User::query()->first();

        if ($branches->isEmpty() || $categories->isEmpty()) {
            return;
        }

        foreach ($branches as $branch) {
            for ($i = 1; $i <= 3; $i++) {
                $documentCode = 'DOC-' . str_pad((string) $branch->id, 2, '0', STR_PAD_LEFT) . '-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT);
                $title = 'Seed Document ' . $branch->id . '-' . $i;
                $prefix = $prefixes->firstWhere('department_id', optional($branch->departments()->first())->id) ?? $prefixes->first();

                $verificationToken = hash('sha256', 'verify-' . $documentCode);
                $qrToken = hash('sha256', 'qr-' . $documentCode);

                Document::updateOrCreate(
                    ['document_code' => $documentCode],
                    [
                        'branch_id' => $branch->id,
                        'document_category_id' => $categories[($i - 1) % $categories->count()]->id,
                        'document_prefix_id' => $prefix?->id,
                        'created_by' => $creator?->id,
                        'updated_by' => $creator?->id,
                        'verification_token' => $verificationToken,
                        'title' => $title,
                        'description' => 'Seeded document for testing API.',
                        'expiration_date' => now()->addYear()->toDateString(),
                        'status' => 'published',
                        'visibility' => 'public',
                        'file_name' => strtolower(str_replace(' ', '_', $title)) . '.pdf',
                        'file_type' => 'pdf',
                        'file_size' => 102400,
                        'mime_type' => 'application/pdf',
                        'file_path' => null,
                        'qr_token' => $qrToken,
                        'qr_code_path' => null,
                    ]
                );
            }
        }
    }
}
