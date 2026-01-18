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
        // 1. Add new columns to items if not exists
        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'plant_id_new')) {
                $table->foreignUuid('plant_id_new')->nullable()->after('plant')->constrained('plants')->restrictOnDelete();
            }
            if (!Schema::hasColumn('items', 'category_uuid')) {
                $table->uuid('category_uuid')->nullable()->after('category_id');
            }
        });

        // 2. Map data
        $items = DB::table('items')->get();
        $plants = DB::table('plants')->get()->pluck('id', 'code');
        $categories = DB::table('categories')->get()->pluck('uuid', 'id');

        foreach ($items as $item) {
            $update = [];
            if (empty($item->plant_id_new) && !empty($item->plant)) {
                $plantCode = strtolower($item->plant);
                $update['plant_id_new'] = $plants[$plantCode] ?? null;
            }
            if (empty($item->category_uuid) && !empty($item->category_id)) {
                $update['category_uuid'] = $categories[$item->category_id] ?? null;
            }

            if (!empty($update)) {
                DB::table('items')->where('id', $item->id)->update($update);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'plant_id_new')) {
                $table->dropForeign(['plant_id_new']);
                $table->dropColumn('plant_id_new');
            }
            if (Schema::hasColumn('items', 'category_uuid')) {
                $table->dropColumn('category_uuid');
            }
        });
    }
};
