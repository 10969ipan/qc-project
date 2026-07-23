<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('incoming_part_arrivals')) {
            try {
                Schema::table('incoming_part_arrivals', function (Blueprint $table) {
                    $table->dropForeign(['plant_id']);
                });
            } catch (\Throwable $e) {}

            try {
                Schema::table('incoming_part_arrivals', function (Blueprint $table) {
                    $table->dropForeign(['item_id']);
                });
            } catch (\Throwable $e) {}

            DB::statement('ALTER TABLE incoming_part_arrivals MODIFY plant_id CHAR(36) NULL');
            DB::statement('ALTER TABLE incoming_part_arrivals MODIFY item_id CHAR(36) NOT NULL');

            Schema::table('incoming_part_arrivals', function (Blueprint $table) {
                $table->foreign('plant_id')->references('id')->on('plants')->onDelete('set null');
                $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('incoming_part_arrivals')) {
            try {
                Schema::table('incoming_part_arrivals', function (Blueprint $table) {
                    $table->dropForeign(['plant_id']);
                    $table->dropForeign(['item_id']);
                });
            } catch (\Throwable $e) {}
        }
    }
};
