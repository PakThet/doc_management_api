<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            // Main Info
            $table->uuid('verification_token')->unique();
            $table->string('title');
            $table->string('document_code')->unique();
            $table->foreignId('document_prefix_id')
                ->nullable()
                ->constrained('document_prefixes')
                ->nullOnDelete();

            $table->text('description')->nullable();

            $table->date('expiration_date')->nullable();
            $table->enum('verification_status', ['inactive', 'active'])
                ->default('active');

            // File Info
            $table->string('file_path')->nullable();
            $table->string('file_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('qr_code_path')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('verification_token');
            $table->index('document_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
