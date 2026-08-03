<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('durability_thickness_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('durability_thickness_reports', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('analis_porecount_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('durability_thickness_reports', function (Blueprint $table) {
            if (Schema::hasColumn('durability_thickness_reports', 'updated_by')) {
                $table->dropColumn('updated_by');
            }
        });
    }
};
