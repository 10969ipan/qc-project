<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('durability_thickness_reports', function (Blueprint $table) {
            $table->string('result_judgment')->nullable()->after('tanggal_cek');
        });
    }

    public function down(): void
    {
        Schema::table('durability_thickness_reports', function (Blueprint $table) {
            $table->dropColumn('result_judgment');
        });
    }
};
