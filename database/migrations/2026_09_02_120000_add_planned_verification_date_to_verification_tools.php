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
        if (!Schema::hasColumn('verification_tools', 'planned_verification_date')) {
            Schema::table('verification_tools', function (Blueprint $table) {
                $table->date('planned_verification_date')->nullable()->after('verification_frequency');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('verification_tools', 'planned_verification_date')) {
            Schema::table('verification_tools', function (Blueprint $table) {
                $table->dropColumn('planned_verification_date');
            });
        }
    }
};
