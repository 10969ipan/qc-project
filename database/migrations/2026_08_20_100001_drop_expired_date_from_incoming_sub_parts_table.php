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
            if (Schema::hasColumn('incoming_sub_parts', 'expired_date')) {
                $table->dropColumn('expired_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incoming_sub_parts', function (Blueprint $table) {
            if (!Schema::hasColumn('incoming_sub_parts', 'expired_date')) {
                $table->date('expired_date')->nullable();
            }
        });
    }
};
