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
        $tables = ['sub_assy_checksheets', 'plating_checksheets', 'double_tape_checksheets'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                if (!Schema::hasColumn($table->getTable(), 'qrcode')) {
                    $table->text('qrcode')->nullable()->after('item_id');
                    $table->string('part_code')->nullable()->after('qrcode');
                    $table->string('supplier_id')->nullable()->after('part_code');
                    $table->string('quantity')->nullable()->after('supplier_id');
                    $table->string('unique_code_id')->nullable()->after('quantity');
                    $table->string('sap_code')->nullable()->after('unique_code_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['sub_assy_checksheets', 'plating_checksheets', 'double_tape_checksheets'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn(['qrcode', 'part_code', 'supplier_id', 'quantity', 'unique_code_id', 'sap_code']);
            });
        }
    }
};
