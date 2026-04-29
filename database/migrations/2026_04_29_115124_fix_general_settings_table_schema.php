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
            // Add missing columns if table exists - Do them one by one to avoid position errors
            if (!Schema::hasColumn('general_settings', 'plant_code')) {
                Schema::table('general_settings', function (Blueprint $table) {
                    $table->string('plant_code')->nullable()->after('key');
                });
            }
            
            if (!Schema::hasColumn('general_settings', 'category')) {
                Schema::table('general_settings', function (Blueprint $table) {
                    // Only use after if plant_code exists, otherwise just add
                    if (Schema::hasColumn('general_settings', 'plant_code')) {
                        $table->string('category')->nullable()->after('plant_code');
                    } else {
                        $table->string('category')->nullable();
                    }
                });
            }

            if (!Schema::hasColumn('general_settings', 'description')) {
                Schema::table('general_settings', function (Blueprint $table) {
                    $table->text('description')->nullable();
                });
            }
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
