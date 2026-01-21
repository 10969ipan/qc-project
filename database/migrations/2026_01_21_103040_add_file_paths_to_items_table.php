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
        Schema::table('items', function (Blueprint $table) {
            $table->json('file_paths')->nullable()->after('file_path');
        });

        // Migrate existing data
        $items = DB::table('items')->whereNotNull('file_path')->get();
        foreach ($items as $item) {
            DB::table('items')->where('id', $item->id)->update([
                'file_paths' => json_encode([$item->file_path])
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('file_paths');
        });
    }
};
