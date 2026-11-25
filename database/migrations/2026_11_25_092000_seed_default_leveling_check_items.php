<?php

use App\Models\LevelingCheckItem;
use Database\Seeders\LevelingCheckItemSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('leveling_check_items')) {
            return;
        }

        // Idempoten: hanya inject 47 Master Point Check default jika tabel masih kosong (aman untuk production)
        if (LevelingCheckItem::count() === 0) {
            (new LevelingCheckItemSeeder())->run();
            Cache::forget('leveling_active_check_items_v1');
        }
    }

    public function down(): void
    {
        // Sengaja tidak menghapus data master point check saat rollback demi keamanan data production
    }
};
