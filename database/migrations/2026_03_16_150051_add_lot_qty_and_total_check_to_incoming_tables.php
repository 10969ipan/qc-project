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
        Schema::table('incoming_parts', function (Blueprint $table) {
            if (!Schema::hasColumn('incoming_parts', 'lot_qty')) {
                $table->integer('lot_qty')->nullable()->after('shift');
            }
        });

        Schema::table('incoming_exports', function (Blueprint $table) {
            if (!Schema::hasColumn('incoming_exports', 'lot_qty')) {
                $table->integer('lot_qty')->nullable()->after('tanggal_delivery');
            }
            if (!Schema::hasColumn('incoming_exports', 'total_check')) {
                $table->integer('total_check')->nullable()->after('lot_qty');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incoming_parts', function (Blueprint $table) {
            $table->dropColumn('lot_qty');
        });

        Schema::table('incoming_exports', function (Blueprint $table) {
            $table->dropColumn(['lot_qty', 'total_check']);
        });
    }
};
