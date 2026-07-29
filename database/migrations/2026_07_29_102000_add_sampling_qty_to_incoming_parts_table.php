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
            if (!Schema::hasColumn('incoming_parts', 'sampling_qty')) {
                $table->integer('sampling_qty')->nullable()->after('total_check');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incoming_parts', function (Blueprint $table) {
            if (Schema::hasColumn('incoming_parts', 'sampling_qty')) {
                $table->dropColumn('sampling_qty');
            }
        });
    }
};
