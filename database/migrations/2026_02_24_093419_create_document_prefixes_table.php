<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_prefixes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('prefix', 20)->unique();
            $table->string('separator', 5)->default('-');
            $table->string('format'); // e.g., {PREFIX}{SEPARATOR}{YEAR}{SEPARATOR}{NUMBER}
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_default')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('is_default');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_prefixes');
    }
};