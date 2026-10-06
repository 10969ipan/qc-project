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
        if (Schema::hasTable('durability_thickness_reports') && !Schema::hasColumn('durability_thickness_reports', 'approval_methods')) {
            Schema::table('durability_thickness_reports', function (Blueprint $table) {
                $table->json('approval_methods')->nullable()->after('updated_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('durability_thickness_reports') && Schema::hasColumn('durability_thickness_reports', 'approval_methods')) {
            Schema::table('durability_thickness_reports', function (Blueprint $table) {
                $table->dropColumn('approval_methods');
            });
        }
    }
};
