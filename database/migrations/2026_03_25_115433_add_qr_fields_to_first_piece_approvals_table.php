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
        Schema::table('first_piece_approvals', function (Blueprint $table) {
            $table->string('qrcode')->nullable()->after('item_id');
            $table->string('part_code')->nullable()->after('qrcode');
            $table->string('supplier_id')->nullable()->after('part_code');
            $table->integer('quantity')->nullable()->after('supplier_id');
            $table->string('unique_code_id')->nullable()->after('quantity');
            $table->string('sap_code')->nullable()->after('unique_code_id');
            $table->foreignId('user_id')->nullable()->after('sap_code')->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('first_piece_approvals', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['qrcode', 'part_code', 'supplier_id', 'quantity', 'unique_code_id', 'sap_code', 'user_id']);
        });
    }
};
