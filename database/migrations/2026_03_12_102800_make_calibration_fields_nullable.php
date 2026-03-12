<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('calibration_tools', function (Blueprint $table) {
            $table->date('tanggal_beli')->nullable()->change();
            $table->string('range')->nullable()->change();
            $table->string('resolusi')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calibration_tools', function (Blueprint $table) {
            $table->date('tanggal_beli')->nullable(false)->change();
            $table->string('range')->nullable(false)->change();
            $table->string('resolusi')->nullable(false)->change();
        });
    }
};
