<?php
// database/migrations/2024_01_01_000005_create_employees_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('branch_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('department_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->string('employee_code')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone', 20)->nullable()->unique();
            $table->text('address')->nullable();
            $table->enum('status', ['active', 'inactive', 'terminated', 'on_leave'])
                ->default('active');
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'intern', 'temporary'])
                ->default('full_time');
            $table->date('date_of_birth')->nullable();
            $table->date('join_date');
            $table->date('probation_end_date')->nullable();
            $table->date('confirmation_date')->nullable();
            $table->date('resignation_date')->nullable();
            $table->date('exit_date')->nullable();
            $table->string('position');
            $table->decimal('salary', 15, 2)->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->json('bank_details')->nullable();
            $table->json('documents')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
            $table->index(['branch_id', 'status']);
            $table->index('join_date');
            // $table->fullText(['first_name', 'last_name', 'email']);
        });

        
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};