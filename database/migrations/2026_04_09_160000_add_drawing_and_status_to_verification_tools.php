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
            $table->string('drawing')->default('TIDAK ADA')->after('verification_type');
            $table->string('tool_status')->default('AKTIF')->after('tool_judgment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('verification_tools', function (Blueprint $table) {
            $table->dropColumn(['drawing', 'tool_status']);
        });
    }
};
