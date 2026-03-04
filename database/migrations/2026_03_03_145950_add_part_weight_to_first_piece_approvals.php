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
            $table->string('part_weight')->nullable()->after('code_machine');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('first_piece_approvals', function (Blueprint $table) {
            $table->dropColumn('part_weight');
        });
    }
};
