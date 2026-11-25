<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('leveling_recap_approvals')) {
            return;
        }

        Schema::create('leveling_recap_approvals', function (Blueprint $table) {
            $table->id();
            $table->string('plant_code', 32);
            $table->unsignedSmallInteger('year');
            $table->unsignedBigInteger('inspector_id');

            $table->unsignedBigInteger('auditor_approved_by')->nullable();
            $table->timestamp('auditor_approved_at')->nullable();

            $table->unsignedBigInteger('leader_approved_by')->nullable();
            $table->timestamp('leader_approved_at')->nullable();

            $table->unsignedBigInteger('spv_approved_by')->nullable();
            $table->timestamp('spv_approved_at')->nullable();

            $table->unsignedBigInteger('asst_mgr_approved_by')->nullable();
            $table->timestamp('asst_mgr_approved_at')->nullable();

            $table->timestamps();

            $table->unique(['plant_code', 'year', 'inspector_id'], 'lvl_recap_appr_unique');
            $table->index(['plant_code', 'year'], 'lvl_recap_appr_plant_year_idx');
            $table->foreign('inspector_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leveling_recap_approvals');
    }
};
