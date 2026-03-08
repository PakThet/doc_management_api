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

    $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
    $table->foreignId('document_category_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('document_prefix_id')->nullable()->constrained()->nullOnDelete();

    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
    $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

    $table->string('document_code')->unique();

    

    $table->string('title');
    $table->text('description')->nullable();

    $table->date('expiration_date')->nullable();

    $table->enum('status', ['draft','published','archived','expired'])->default('draft');
    $table->enum('visibility', ['public','private','restricted'])->default('private');

    $table->string('file_name')->nullable();
    $table->string('file_type')->nullable();
    $table->integer('file_size')->nullable();
    $table->string('mime_type')->nullable();
    $table->string('file_path')->nullable();

    $table->boolean('is_confidential')->default(false);

    $table->string('verification_token')->nullable()->unique();
    $table->string('qr_token')->nullable()->unique();
    $table->string('qr_code_path')->nullable();

    $table->timestamps();
    $table->softDeletes();

    $table->index(['branch_id','status']);
});
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};