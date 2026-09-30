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
            // CASS Evidence
            $table->string('evidence_before_cass')->nullable();
            $table->timestamp('evidence_before_cass_uploaded_at')->nullable();
            $table->string('evidence_after_cass')->nullable();
            $table->timestamp('evidence_after_cass_uploaded_at')->nullable();
            $table->string('evidence_after_trial_cass')->nullable();
            $table->timestamp('evidence_after_trial_cass_uploaded_at')->nullable();

            // Salt Spray Evidence
            $table->string('evidence_before_salt_spray')->nullable();
            $table->timestamp('evidence_before_salt_spray_uploaded_at')->nullable();
            $table->string('evidence_after_salt_spray')->nullable();
            $table->timestamp('evidence_after_salt_spray_uploaded_at')->nullable();
            $table->string('evidence_after_trial_salt_spray')->nullable();
            $table->timestamp('evidence_after_trial_salt_spray_uploaded_at')->nullable();

            // Porecount Evidence
            $table->string('evidence_before_porecount')->nullable();
            $table->timestamp('evidence_before_porecount_uploaded_at')->nullable();
            $table->string('evidence_after_porecount')->nullable();
            $table->timestamp('evidence_after_porecount_uploaded_at')->nullable();
            $table->string('evidence_after_trial_porecount')->nullable();
            $table->timestamp('evidence_after_trial_porecount_uploaded_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('durability_thickness_reports', function (Blueprint $table) {
            $table->dropColumn([
                'evidence_before_cass', 'evidence_before_cass_uploaded_at',
                'evidence_after_cass', 'evidence_after_cass_uploaded_at',
                'evidence_after_trial_cass', 'evidence_after_trial_cass_uploaded_at',
                
                'evidence_before_salt_spray', 'evidence_before_salt_spray_uploaded_at',
                'evidence_after_salt_spray', 'evidence_after_salt_spray_uploaded_at',
                'evidence_after_trial_salt_spray', 'evidence_after_trial_salt_spray_uploaded_at',
                
                'evidence_before_porecount', 'evidence_before_porecount_uploaded_at',
                'evidence_after_porecount', 'evidence_after_porecount_uploaded_at',
                'evidence_after_trial_porecount', 'evidence_after_trial_porecount_uploaded_at',
            ]);
        });
    }
};
