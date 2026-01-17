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
        Schema::dropIfExists('in_process_checksheets_backup');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No easy way to restore a dropped table without a backup
    }
};
