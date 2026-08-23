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
        Schema::table('sub_assy_checksheets', function (Blueprint $table) {
            if (!Schema::hasColumn('sub_assy_checksheets', 'injection_date')) {
                $table->date('injection_date')->nullable()->after('item_id');
            }
            if (!Schema::hasColumn('sub_assy_checksheets', 'injection_shift')) {
                $table->string('injection_shift')->nullable()->after('injection_date');
            }
            if (!Schema::hasColumn('sub_assy_checksheets', 'injection_initials')) {
                $table->string('injection_initials')->nullable()->after('injection_shift');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_assy_checksheets', function (Blueprint $table) {
            $table->dropColumn([
                'injection_date',
                'injection_shift',
                'injection_initials',
            ]);
        });
    }
};
