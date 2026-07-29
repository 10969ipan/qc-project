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
        Schema::table('incoming_parts', function (Blueprint $table) {
            $table->integer('qty_balance_sisa')->nullable()->after('sampling_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incoming_parts', function (Blueprint $table) {
            $table->dropColumn('qty_balance_sisa');
        });
    }
};
