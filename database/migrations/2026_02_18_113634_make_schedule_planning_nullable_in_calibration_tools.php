<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('calibration_tools', function (Blueprint $table) {
            $table->date('schedule_planning')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('calibration_tools', function (Blueprint $table) {
            $table->date('schedule_planning')->nullable(false)->change();
        });
    }
};
