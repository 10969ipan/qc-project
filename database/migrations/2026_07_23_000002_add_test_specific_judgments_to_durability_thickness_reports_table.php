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
            if (!Schema::hasColumn('durability_thickness_reports', 'result_judgment_corrodkote')) {
                $table->string('result_judgment_corrodkote')->nullable()->after('result_judgment');
            }
            if (!Schema::hasColumn('durability_thickness_reports', 'result_judgment_cass')) {
                $table->string('result_judgment_cass')->nullable()->after('result_judgment_corrodkote');
            }
            if (!Schema::hasColumn('durability_thickness_reports', 'result_judgment_salt_spray')) {
                $table->string('result_judgment_salt_spray')->nullable()->after('result_judgment_cass');
            }
            if (!Schema::hasColumn('durability_thickness_reports', 'result_judgment_porecount')) {
                $table->string('result_judgment_porecount')->nullable()->after('result_judgment_salt_spray');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('durability_thickness_reports', function (Blueprint $table) {
            $table->dropColumn([
                'result_judgment_corrodkote',
                'result_judgment_cass',
                'result_judgment_salt_spray',
                'result_judgment_porecount'
            ]);
        });
    }
};
