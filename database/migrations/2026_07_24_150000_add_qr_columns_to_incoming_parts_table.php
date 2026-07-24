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
        if (Schema::hasTable('incoming_parts') && !Schema::hasColumn('incoming_parts', 'unique_code_id')) {
            Schema::table('incoming_parts', function (Blueprint $table) {
                $table->string('part_code')->nullable()->after('item_id');
                $table->string('supplier_id')->nullable()->after('part_code');
                $table->integer('quantity')->nullable()->after('supplier_id');
                $table->string('unique_code_id')->nullable()->after('quantity');
                $table->string('sap_code')->nullable()->after('unique_code_id');
                $table->string('scan_method')->nullable()->after('sap_code');
                $table->text('qrcode')->nullable()->after('scan_method');
                $table->integer('cycle_time')->nullable()->after('operator_initials');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('incoming_parts') && Schema::hasColumn('incoming_parts', 'unique_code_id')) {
            Schema::table('incoming_parts', function (Blueprint $table) {
                $table->dropColumn([
                    'part_code',
                    'supplier_id',
                    'quantity',
                    'unique_code_id',
                    'sap_code',
                    'scan_method',
                    'qrcode',
                    'cycle_time'
                ]);
            });
        }
    }
};
