<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leveling_audits', function (Blueprint $table) {
            if (!Schema::hasColumn('leveling_audits', 'meja_mesin')) {
                $table->string('meja_mesin', 80)->nullable()->after('bagian');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leveling_audits', function (Blueprint $table) {
            if (Schema::hasColumn('leveling_audits', 'meja_mesin')) {
                $table->dropColumn('meja_mesin');
            }
        });
    }
};
