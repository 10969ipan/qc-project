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
        // Ensure table exists
        if (!Schema::hasTable('general_settings')) {
            Schema::create('general_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->string('plant_code')->nullable();
                $table->string('category')->nullable();
                $table->text('value')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        } else {
            // Add missing columns if table exists
            Schema::table('general_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('general_settings', 'category')) {
                    $table->string('category')->nullable()->after('plant_code');
                }
                
                if (!Schema::hasColumn('general_settings', 'plant_code')) {
                    $table->string('plant_code')->nullable()->after('key');
                }

                if (!Schema::hasColumn('general_settings', 'description')) {
                    $table->text('description')->nullable()->after('value');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't want to drop the whole table if it existed before, 
        // but we can drop the columns we added.
        if (Schema::hasTable('general_settings')) {
            Schema::table('general_settings', function (Blueprint $table) {
                if (Schema::hasColumn('general_settings', 'category')) {
                    $table->dropColumn('category');
                }
            });
        }
    }
};
