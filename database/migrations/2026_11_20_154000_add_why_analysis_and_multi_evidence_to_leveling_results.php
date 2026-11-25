<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leveling_audit_results', function (Blueprint $table) {
            $table->json('evidence_paths')->nullable()->after('evidence_path');
            $table->text('why_1')->nullable()->after('ng_status');
            $table->text('why_2')->nullable()->after('why_1');
            $table->text('why_3')->nullable()->after('why_2');
            $table->text('root_cause')->nullable()->after('why_3');
            $table->json('corrective_evidence_paths')->nullable()->after('corrective_evidence_path');
        });

        // Backfill foto lama ke kolom JSON array agar tetap tampil mulus
        $rows = DB::table('leveling_audit_results')
            ->whereNotNull('evidence_path')
            ->orWhereNotNull('corrective_evidence_path')
            ->get(['id', 'evidence_path', 'corrective_evidence_path']);

        foreach ($rows as $row) {
            $upd = [];
            if (!empty($row->evidence_path)) {
                $upd['evidence_paths'] = json_encode([$row->evidence_path]);
            }
            if (!empty($row->corrective_evidence_path)) {
                $upd['corrective_evidence_paths'] = json_encode([$row->corrective_evidence_path]);
            }
            if (!empty($upd)) {
                DB::table('leveling_audit_results')->where('id', $row->id)->update($upd);
            }
        }
    }

    public function down(): void
    {
        Schema::table('leveling_audit_results', function (Blueprint $table) {
            $table->dropColumn([
                'evidence_paths',
                'why_1',
                'why_2',
                'why_3',
                'root_cause',
                'corrective_evidence_paths',
            ]);
        });
    }
};
