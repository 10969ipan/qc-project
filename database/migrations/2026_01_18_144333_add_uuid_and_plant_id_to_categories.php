<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add new columns to categories if not exists
        if (!Schema::hasColumn('categories', 'uuid')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->uuid('uuid')->nullable()->after('id');
            });
        }

        if (!Schema::hasColumn('categories', 'plant_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->foreignUuid('plant_id')->nullable()->after('plant')->constrained('plants')->nullOnDelete();
            });
        }

        // 2. Populate UUIDs and map plant_id
        $categories = DB::table('categories')->get();
        $plants = DB::table('plants')->get()->pluck('id', 'code');

        foreach ($categories as $category) {
            $update = [];
            if (empty($category->uuid)) {
                $update['uuid'] = (string) Str::uuid();
            }
            if (empty($category->plant_id) && !empty($category->plant)) {
                $plantCode = strtolower($category->plant);
                $update['plant_id'] = $plants[$plantCode] ?? null;
            }

            if (!empty($update)) {
                DB::table('categories')->where('id', $category->id)->update($update);
            }
        }

        // 3. Make uuid non-nullable
        // To avoid MySQL errors about FKs, we use a raw statement if needed or ensure no FK points to uuid (which shouldn't)
        Schema::table('categories', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['plant_id']);
            $table->dropColumn(['uuid', 'plant_id']);
        });
    }
};
