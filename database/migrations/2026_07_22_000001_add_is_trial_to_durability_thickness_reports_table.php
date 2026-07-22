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
        Schema::table('durability_thickness_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('durability_thickness_reports', 'is_trial')) {
                $table->boolean('is_trial')->default(false)->after('analis_id')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('durability_thickness_reports', function (Blueprint $table) {
            if (Schema::hasColumn('durability_thickness_reports', 'is_trial')) {
                $table->dropColumn('is_trial');
            }
        });
    }
};
