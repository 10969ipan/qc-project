<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('operator_compliance_items', 'bagian')) {
            Schema::table('operator_compliance_items', function (Blueprint $table) {
                $table->string('bagian', 100)->nullable()->after('id');
            });
        }

        try {
            Schema::table('operator_compliance_items', function (Blueprint $table) {
                $table->index(['is_active', 'bagian', 'order_no'], 'idx_op_comp_items_bagian_order');
            });
        } catch (\Throwable $e) {
            // Index already exists
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('operator_compliance_items', 'bagian')) {
            Schema::table('operator_compliance_items', function (Blueprint $table) {
                $table->dropIndex('idx_op_comp_items_bagian_order');
                $table->dropColumn('bagian');
            });
        }
    }
};
