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
        Schema::table('sub_assy_checksheets', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('plant_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('in_process_checksheets', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('plant_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('in_process_checksheets', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('sub_assy_checksheets', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
