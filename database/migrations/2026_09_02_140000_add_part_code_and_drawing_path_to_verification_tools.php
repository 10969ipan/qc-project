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
            if (!Schema::hasColumn('verification_tools', 'part_code')) {
                $table->string('part_code')->nullable()->after('no_part');
            }
            if (!Schema::hasColumn('verification_tools', 'drawing_path')) {
                $table->string('drawing_path')->nullable()->after('drawing');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('verification_tools', function (Blueprint $table) {
            if (Schema::hasColumn('verification_tools', 'part_code')) {
                $table->dropColumn('part_code');
            }
            if (Schema::hasColumn('verification_tools', 'drawing_path')) {
                $table->dropColumn('drawing_path');
            }
        });
    }
};
