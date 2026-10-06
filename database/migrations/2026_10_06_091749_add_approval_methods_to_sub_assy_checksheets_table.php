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
        if (Schema::hasTable('sub_assy_checksheets') && !Schema::hasColumn('sub_assy_checksheets', 'approval_methods')) {
            Schema::table('sub_assy_checksheets', function (Blueprint $table) {
                $table->json('approval_methods')->nullable()->after('updated_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sub_assy_checksheets') && Schema::hasColumn('sub_assy_checksheets', 'approval_methods')) {
            Schema::table('sub_assy_checksheets', function (Blueprint $table) {
                $table->dropColumn('approval_methods');
            });
        }
    }
};
