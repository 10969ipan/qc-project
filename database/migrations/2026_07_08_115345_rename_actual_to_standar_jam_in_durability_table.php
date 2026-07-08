<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('durability_thickness_reports', function (Blueprint $table) {
            $table->renameColumn('actual_corrodkote', 'standar_jam_corrodkote');
            $table->renameColumn('actual_cass', 'standar_jam_cass');
            $table->renameColumn('actual_salt_spray', 'standar_jam_salt_spray');
        });
    }

    public function down(): void
    {
        Schema::table('durability_thickness_reports', function (Blueprint $table) {
            $table->renameColumn('standar_jam_corrodkote', 'actual_corrodkote');
            $table->renameColumn('standar_jam_cass', 'actual_cass');
            $table->renameColumn('standar_jam_salt_spray', 'actual_salt_spray');
        });
    }
};
