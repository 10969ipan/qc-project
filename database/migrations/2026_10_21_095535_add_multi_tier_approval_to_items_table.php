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
        Schema::table('items', function (Blueprint $table) {
            $table->unsignedBigInteger('spv_approved_by')->nullable()->after('approval_status');
            $table->timestamp('spv_approved_at')->nullable()->after('spv_approved_by');

            $table->unsignedBigInteger('asst_mgr_approved_by')->nullable()->after('spv_approved_at');
            $table->timestamp('asst_mgr_approved_at')->nullable()->after('asst_mgr_approved_by');

            $table->unsignedBigInteger('mgr_approved_by')->nullable()->after('asst_mgr_approved_at');
            $table->timestamp('mgr_approved_at')->nullable()->after('mgr_approved_by');

            $table->foreign('spv_approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('asst_mgr_approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('mgr_approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['spv_approved_by']);
            $table->dropForeign(['asst_mgr_approved_by']);
            $table->dropForeign(['mgr_approved_by']);
            $table->dropColumn([
                'spv_approved_by', 'spv_approved_at',
                'asst_mgr_approved_by', 'asst_mgr_approved_at',
                'mgr_approved_by', 'mgr_approved_at'
            ]);
        });
    }
};
