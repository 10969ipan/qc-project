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
            $table->integer('total_claims')->default(0)->after('target_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_claims', function (Blueprint $table) {
            $table->dropColumn('total_claims');
        });
    }
};
