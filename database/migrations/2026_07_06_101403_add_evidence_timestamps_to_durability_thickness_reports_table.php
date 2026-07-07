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
            $table->timestamp('evidence_before_uploaded_at')->nullable()->after('evidence_before');
            $table->timestamp('evidence_after_uploaded_at')->nullable()->after('evidence_after');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('durability_thickness_reports', function (Blueprint $table) {
            $table->dropColumn(['evidence_before_uploaded_at', 'evidence_after_uploaded_at']);
        });
    }
};
