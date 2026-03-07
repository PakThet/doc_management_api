<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('document_category_id')->references('id')->on('document_categories')->onDelete('set null');
            $table->foreign('document_prefix_id')->references('id')->on('document_prefixes')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['document_category_id']);
            $table->dropForeign(['document_prefix_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
        });
    }
};
