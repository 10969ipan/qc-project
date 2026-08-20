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
        Schema::table('incoming_sub_parts', function (Blueprint $table) {
            if (!Schema::hasColumn('incoming_sub_parts', 'cycle_time')) {
                $table->integer('cycle_time')->nullable()->after('operator_initials');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incoming_sub_parts', function (Blueprint $table) {
            if (Schema::hasColumn('incoming_sub_parts', 'cycle_time')) {
                $table->dropColumn('cycle_time');
            }
        });
    }
};
