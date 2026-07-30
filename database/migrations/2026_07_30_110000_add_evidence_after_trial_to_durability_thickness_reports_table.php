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
        Schema::table('durability_thickness_reports', function (Blueprint $table) {
            $table->string('evidence_after_trial')->nullable()->after('evidence_after_uploaded_at');
            $table->timestamp('evidence_after_trial_uploaded_at')->nullable()->after('evidence_after_trial');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('durability_thickness_reports', function (Blueprint $table) {
            $table->dropColumn(['evidence_after_trial', 'evidence_after_trial_uploaded_at']);
        });
    }
};
