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
        Schema::table('verification_tools', function (Blueprint $table) {
            if (!Schema::hasColumn('verification_tools', 'dimension_standards')) {
                $table->json('dimension_standards')->nullable()->after('drawing_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('verification_tools', function (Blueprint $table) {
            if (Schema::hasColumn('verification_tools', 'dimension_standards')) {
                $table->dropColumn('dimension_standards');
            }
        });
    }
};
