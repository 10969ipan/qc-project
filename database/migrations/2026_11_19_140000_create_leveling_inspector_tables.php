<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leveling_check_items', function (Blueprint $table) {
            $table->id();
            $table->string('prinsip', 30);
            $table->unsignedTinyInteger('level');
            $table->unsignedInteger('order_no')->default(1);
            $table->text('point_check');
            $table->text('guidance')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'prinsip', 'level', 'order_no'], 'idx_lvl_items_active_group');
        });

        Schema::create('leveling_audits', function (Blueprint $table) {
            $table->id();
            $table->string('plant_code', 30);
            $table->date('audit_date');
            $table->foreignId('inspector_id')->constrained('users')->cascadeOnDelete();
            $table->string('bagian', 120)->nullable();
            $table->char('item_id', 36)->nullable();
            $table->foreign('item_id')->references('id')->on('items')->nullOnDelete();
            $table->string('part_name', 255);
            $table->string('part_number', 100)->nullable();
            $table->foreignId('auditor_id')->constrained('users')->cascadeOnDelete();
            $table->string('auditor_name', 150);
            $table->unsignedSmallInteger('total_ok')->default(0);
            $table->unsignedSmallInteger('total_ng')->default(0);
            $table->decimal('score', 5, 2)->nullable();
            $table->timestamps();

            $table->index(['plant_code', 'audit_date'], 'idx_lvl_audits_plant_date');
            $table->index(['plant_code', 'inspector_id', 'audit_date'], 'idx_lvl_audits_plant_insp_date');
            $table->unique(['plant_code', 'audit_date', 'inspector_id', 'item_id'], 'uq_lvl_audit_plant_date_insp_item');
        });

        Schema::create('leveling_audit_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_id')->constrained('leveling_audits')->cascadeOnDelete();
            $table->foreignId('check_item_id')->nullable()->constrained('leveling_check_items')->nullOnDelete();
            $table->string('prinsip', 30);
            $table->unsignedTinyInteger('level');
            $table->text('point_check');
            $table->enum('result', ['OK', 'NG'])->nullable();
            $table->string('evidence_path', 255)->nullable();
            $table->enum('ng_status', ['open', 'waiting_verify', 'closed'])->nullable();
            $table->text('corrective_action')->nullable();
            $table->string('corrective_evidence_path', 255)->nullable();
            $table->date('corrective_date')->nullable();
            $table->foreignId('corrected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('reject_reason')->nullable();
            $table->timestamps();

            $table->unique(['audit_id', 'check_item_id'], 'uq_lvl_res_audit_item');
            $table->index(['ng_status', 'audit_id'], 'idx_lvl_res_ng_status_audit');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leveling_audit_results');
        Schema::dropIfExists('leveling_audits');
        Schema::dropIfExists('leveling_check_items');
    }
};
