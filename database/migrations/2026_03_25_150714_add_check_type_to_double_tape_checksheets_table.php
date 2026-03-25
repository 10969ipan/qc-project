<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('double_tape_checksheets', function (Blueprint $table) {
            $table->string('check_type')->default('sampling')->after('shift'); // sampling / fullcheck
        });
    }

    public function down(): void
    {
        Schema::table('double_tape_checksheets', function (Blueprint $table) {
            $table->dropColumn('check_type');
        });
    }
};
