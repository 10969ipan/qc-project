<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('durability_thickness_reports', function (Blueprint $table) {
            // Explicit FK: DATA 2 (is_trial=true) points to its parent DATA 1 (is_trial=false)
            $table->unsignedBigInteger('data1_id')->nullable()->after('is_trial');
        });
    }

    public function down(): void
    {
        Schema::table('durability_thickness_reports', function (Blueprint $table) {
            $table->dropColumn('data1_id');
        });
    }
};
