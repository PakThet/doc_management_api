<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            if (! Schema::hasColumn('roles', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('id');
                $table->index('branch_id');
            }

            if (! Schema::hasColumn('roles', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('permissions', function (Blueprint $table) {
            if (! Schema::hasColumn('permissions', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'branch_id')) {
                $table->dropIndex(['branch_id']);
                $table->dropColumn('branch_id');
            }

            if (Schema::hasColumn('roles', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('permissions', function (Blueprint $table) {
            if (Schema::hasColumn('permissions', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
