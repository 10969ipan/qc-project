<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('items', 'uuid')) {
            Schema::table('items', function (Blueprint $table) {
                $table->uuid('uuid')->nullable()->after('id');
            });

            $items = DB::table('items')->get();
            foreach ($items as $item) {
                DB::table('items')->where('id', $item->id)->update([
                    'uuid' => (string) Str::uuid()
                ]);
            }

            Schema::table('items', function (Blueprint $table) {
                $table->uuid('uuid')->nullable(false)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'uuid')) {
                $table->dropColumn('uuid');
            }
        });
    }
};
