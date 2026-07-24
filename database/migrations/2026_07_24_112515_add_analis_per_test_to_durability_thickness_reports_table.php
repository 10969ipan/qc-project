<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('durability_thickness_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('analis_corrodkote_id')->nullable()->after('analis_id');
            $table->unsignedBigInteger('analis_cass_id')->nullable()->after('analis_corrodkote_id');
            $table->unsignedBigInteger('analis_salt_spray_id')->nullable()->after('analis_cass_id');
            $table->unsignedBigInteger('analis_porecount_id')->nullable()->after('analis_salt_spray_id');
        });
    }

    public function down(): void
    {
        Schema::table('durability_thickness_reports', function (Blueprint $table) {
            $table->dropColumn(['analis_corrodkote_id', 'analis_cass_id', 'analis_salt_spray_id', 'analis_porecount_id']);
        });
    }
};
