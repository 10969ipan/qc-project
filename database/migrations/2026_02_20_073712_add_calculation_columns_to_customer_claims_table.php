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
            $table->decimal('total_claim_pcs', 15, 2)->nullable()->after('ppm_value');
            $table->decimal('total_delivery', 15, 2)->nullable()->after('total_claim_pcs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_claims', function (Blueprint $table) {
            $table->dropColumn(['total_claim_pcs', 'total_delivery']);
        });
    }
};
