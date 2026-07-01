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
            $table->unsignedBigInteger('analis_id')->nullable()->after('tanggal_cek');
            $table->text('description')->nullable()->after('analis_id');

            $table->foreign('analis_id', 'fk_dt_reports_analis')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('durability_thickness_reports', function (Blueprint $table) {
            $table->dropForeign('fk_dt_reports_analis');
            $table->dropColumn(['analis_id', 'description']);
        });
    }
};
