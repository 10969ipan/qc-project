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
        Schema::table('first_piece_approvals', function (Blueprint $table) {
            if (!Schema::hasColumn('first_piece_approvals', 'category')) {
                $table->string('category')->nullable()->after('code_machine');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('first_piece_approvals', function (Blueprint $table) {
            if (Schema::hasColumn('first_piece_approvals', 'category')) {
                $table->dropColumn('category');
            }
        });
    }
};
