<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->text('description')->nullable();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('location')->nullable();
            $table->decimal('budget', 15, 2)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['branch_id', 'name']);
            $table->index('parent_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};