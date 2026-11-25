<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leveling_audit_results', function (Blueprint $table) {
            if (!Schema::hasColumn('leveling_audit_results', 'evidence_note')) {
                $table->text('evidence_note')->nullable()->after('evidence_paths');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leveling_audit_results', function (Blueprint $table) {
            if (Schema::hasColumn('leveling_audit_results', 'evidence_note')) {
                $table->dropColumn('evidence_note');
            }
        });
    }
};
