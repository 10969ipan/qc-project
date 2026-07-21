<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customer_claim_records', function (Blueprint $table) {
            $table->decimal('total_irregular', 15, 2)->default(0)->after('total_overtime');
            $table->decimal('total_cost', 15, 2)->default(0)->after('total_irregular');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_claim_records', function (Blueprint $table) {
            $table->dropColumn(['total_irregular', 'total_cost']);
        });
    }
};
