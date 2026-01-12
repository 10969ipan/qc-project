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
        Schema::table('checksheets', function (Blueprint $table) {
            $table->string('line')->nullable()->after('shift');
        });

        Schema::table('in_process_checksheets', function (Blueprint $table) {
            $table->string('code_machine')->nullable()->after('shift');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checksheets', function (Blueprint $table) {
            $table->dropColumn('line');
        });

        Schema::table('in_process_checksheets', function (Blueprint $table) {
            $table->dropColumn('code_machine');
        });
    }
};
