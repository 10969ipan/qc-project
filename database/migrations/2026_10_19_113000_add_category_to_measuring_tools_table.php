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
        if (Schema::hasTable('measuring_tools') && !Schema::hasColumn('measuring_tools', 'category')) {
            Schema::table('measuring_tools', function (Blueprint $table) {
                $table->string('category', 30)->default('numeric')->after('serial_number');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('measuring_tools') && Schema::hasColumn('measuring_tools', 'category')) {
            Schema::table('measuring_tools', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }
    }
};
