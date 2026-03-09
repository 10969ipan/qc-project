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
        Schema::table('in_process_checksheets', function (Blueprint $table) {
            $table->renameColumn('qrcode_id', 'qrcode');
            $table->string('part_code')->nullable()->after('item_id');
            $table->string('supplier_id')->nullable()->after('part_code');
            $table->integer('quantity')->nullable()->after('supplier_id');
            $table->string('unique_code_id')->nullable()->unique()->after('quantity');
            $table->string('sap_code')->nullable()->after('unique_code_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('in_process_checksheets', function (Blueprint $table) {
            $table->renameColumn('qrcode', 'qrcode_id');
            $table->dropColumn(['part_code', 'supplier_id', 'quantity', 'unique_code_id', 'sap_code']);
        });
    }
};
