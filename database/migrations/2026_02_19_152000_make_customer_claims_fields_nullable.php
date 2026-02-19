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
        Schema::table('customer_claims', function (Blueprint $table) {
            $table->decimal('ppm_value', 10, 2)->nullable()->change();
            $table->decimal('target_value', 10, 2)->nullable()->change();
            $table->decimal('total_claims', 10, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_claims', function (Blueprint $table) {
            $table->decimal('ppm_value', 10, 2)->nullable(false)->change();
            $table->decimal('target_value', 10, 2)->nullable(false)->default(0)->change();
            $table->decimal('total_claims', 10, 2)->nullable(false)->default(0)->change();
        });
    }
};
