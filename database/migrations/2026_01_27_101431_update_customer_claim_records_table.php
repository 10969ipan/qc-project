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
        Schema::table('customer_claim_records', function (Blueprint $table) {
            // Remove obsolete columns
            $table->dropColumn(['frek', 'persen_frek', 'total_cost']);

            // Add new columns
            $table->decimal('total_akomodasi', 15, 2)->default(0)->after('action_taken');
            $table->decimal('total_overtime', 15, 2)->default(0)->after('total_akomodasi');
            $table->string('attachment_path')->nullable()->after('status_cm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_claim_records', function (Blueprint $table) {
            $table->dropColumn(['total_akomodasi', 'total_overtime', 'attachment_path']);

            $table->string('frek')->nullable();
            $table->string('persen_frek')->nullable();
            $table->decimal('total_cost', 15, 2)->default(0);
        });
    }
};
