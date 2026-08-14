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
        Schema::table('double_tape_checksheets', function (Blueprint $table) {
            $table->date('injection_date')->nullable()->after('item_id');
            $table->string('injection_shift')->nullable()->after('injection_date');
            $table->string('injection_initials')->nullable()->after('injection_shift');
            $table->date('plating_date')->nullable()->after('injection_initials');
            $table->string('plating_shift')->nullable()->after('plating_date');
            $table->string('plating_initials')->nullable()->after('plating_shift');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('double_tape_checksheets', function (Blueprint $table) {
            $table->dropColumn([
                'injection_date',
                'injection_shift',
                'injection_initials',
                'plating_date',
                'plating_shift',
                'plating_initials',
            ]);
        });
    }
};
