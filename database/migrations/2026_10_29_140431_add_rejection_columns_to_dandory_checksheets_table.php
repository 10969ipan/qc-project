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
        Schema::table('dandory_checksheets', function (Blueprint $table) {
            $table->boolean('is_rejected')->default(false)->after('leader_checked_at');
            $table->text('rejection_remarks')->nullable()->after('is_rejected');
            $table->unsignedBigInteger('rejected_by')->nullable()->after('rejection_remarks');
            $table->string('rejector_name', 100)->nullable()->after('rejected_by');
            $table->timestamp('rejected_at')->nullable()->after('rejector_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dandory_checksheets', function (Blueprint $table) {
            $table->dropColumn(['is_rejected', 'rejection_remarks', 'rejected_by', 'rejector_name', 'rejected_at']);
        });
    }
};
