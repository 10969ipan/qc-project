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
            if (!Schema::hasColumn('durability_thickness_reports', 'description_corrodkote')) {
                $table->text('description_corrodkote')->nullable()->after('description');
            }
            if (!Schema::hasColumn('durability_thickness_reports', 'description_cass')) {
                $table->text('description_cass')->nullable()->after('description_corrodkote');
            }
            if (!Schema::hasColumn('durability_thickness_reports', 'description_salt_spray')) {
                $table->text('description_salt_spray')->nullable()->after('description_cass');
            }
            if (!Schema::hasColumn('durability_thickness_reports', 'description_porecount')) {
                $table->text('description_porecount')->nullable()->after('description_salt_spray');
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
                'description_corrodkote',
                'description_cass',
                'description_salt_spray',
                'description_porecount'
            ]);
        });
    }
};
