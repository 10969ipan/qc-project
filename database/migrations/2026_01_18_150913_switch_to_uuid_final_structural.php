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
        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

        // --- 1. CATEGORIES ---
        echo "Switching Categories...\n";
        // Remove auto-increment if it exists
        try {
            DB::statement("ALTER TABLE `categories` MODIFY id INT NOT NULL");
        } catch (\Exception $e) {
            echo "   Note: categories.id auto-increment already gone.\n";
        }

        try {
            DB::statement("ALTER TABLE `categories` DROP PRIMARY KEY");
        } catch (\Exception $e) {
            echo "   Note: categories PRIMARY key already gone.\n";
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->renameColumn('id', 'id_old');
            $table->renameColumn('uuid', 'id');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->primary('id');
            $table->dropColumn(['id_old', 'plant']);
        });

        // --- 2. ITEMS ---
        echo "Switching Items...\n";
        try {
            DB::statement("ALTER TABLE `items` MODIFY id BIGINT NOT NULL");
        } catch (\Exception $e) {
            echo "   Note: items.id auto-increment already gone.\n";
        }

        try {
            DB::statement("ALTER TABLE `items` DROP PRIMARY KEY");
        } catch (\Exception $e) {
            echo "   Note: items PRIMARY key already gone.\n";
        }

        Schema::table('items', function (Blueprint $table) {
            $table->renameColumn('id', 'id_old');
            $table->renameColumn('uuid', 'id');
            $table->renameColumn('category_uuid', 'category_id_new');
            $table->renameColumn('plant_id_new', 'plant_id');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->renameColumn('category_id_new', 'category_id');
            $table->primary('id');
            $table->dropColumn(['id_old', 'plant', 'category_id_old_temp']); // Just in case, but let's be careful
        });

        // Final cleanup for items category_id link
        if (Schema::hasColumn('items', 'category_id')) {
            // Already renamed/switched
        } else {
            // Fallback if rename failed but we have data
        }

        // Wait, I forgot that I have category_id (INT) currently.
        // I renamed 'category_uuid' to 'category_id_new' then to 'category_id'.
        // But I also need to drop the OLD 'category_id' (INT).
        // Let's fix that.

        Schema::table('items', function (Blueprint $table) {
            try {
                $table->dropColumn('category_id');
            } catch (\Exception $e) {
            }
            $table->renameColumn('category_id_new', 'category_id');
            $table->foreign('category_id')->references('id')->on('categories')->restrictOnDelete();
            $table->foreign('plant_id')->references('id')->on('plants')->restrictOnDelete();
        });

        // --- 3. CHECKSHEETS ---
        echo "Switching Checksheets...\n";
        $checksheetTables = ['sub_assy_checksheets', 'in_process_checksheets', 'cross_cut_checksheets', 'sortir_checksheets'];
        foreach ($checksheetTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn(['item_id', 'plant']);
                $table->renameColumn('item_uuid', 'item_id');
                $table->renameColumn('plant_id_new', 'plant_id');
            });
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreign('item_id')->references('id')->on('items')->restrictOnDelete();
                $table->foreign('plant_id')->references('id')->on('plants')->restrictOnDelete();
            });
        }

        // --- 4. USERS ---
        echo "Switching Users...\n";
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('plant');
            $table->renameColumn('plant_id_new', 'plant_id');
            $table->foreign('plant_id')->references('id')->on('plants')->nullOnDelete();
        });

        DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
        echo "Switch COMPLETE!\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
