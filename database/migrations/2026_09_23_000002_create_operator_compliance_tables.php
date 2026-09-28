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
        // 1. Master Item Audit (Configurable by Admin)
        Schema::create('operator_compliance_items', function (Blueprint $table) {
            $table->id();
            $table->string('prinsip_dasar');
            $table->string('item_check');
            $table->text('standard');
            $table->integer('order_no')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Checksheet Header
        Schema::create('operator_compliance_checksheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Operator / Inspector being audited
            $table->string('plant_code', 50)->default('karawang');
            $table->string('bagian')->nullable();
            $table->integer('month');
            $table->integer('year');
            $table->boolean('leader_checked')->default(false);
            $table->timestamp('leader_checked_at')->nullable();
            $table->foreignId('leader_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('spv_checked')->default(false);
            $table->timestamp('spv_checked_at')->nullable();
            $table->foreignId('spv_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('mgr_checked')->default(false);
            $table->timestamp('mgr_checked_at')->nullable();
            $table->foreignId('mgr_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['user_id', 'plant_code', 'month', 'year'], 'op_comp_header_unique');
        });

        // 3. Matrix Entries (Daily status: OK / NG / null)
        Schema::create('operator_compliance_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checksheet_id')->constrained('operator_compliance_checksheets')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('operator_compliance_items')->onDelete('cascade');
            $table->unsignedTinyInteger('day'); // 1..31
            $table->string('status', 10)->nullable(); // 'OK', 'NG', or null
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['checksheet_id', 'item_id', 'day'], 'op_comp_entry_unique');
        });

        // 4. Problem Log / Action Items
        Schema::create('operator_compliance_problems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checksheet_id')->constrained('operator_compliance_checksheets')->onDelete('cascade');
            $table->foreignId('item_id')->nullable()->constrained('operator_compliance_items')->onDelete('set null');
            $table->date('problem_date');
            $table->text('problem_description');
            $table->text('corrective_action')->nullable();
            $table->string('pic_name')->nullable();
            $table->date('target_date')->nullable();
            $table->string('status', 20)->default('Open'); // 'Open', 'In Progress', 'Closed'
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operator_compliance_problems');
        Schema::dropIfExists('operator_compliance_entries');
        Schema::dropIfExists('operator_compliance_checksheets');
        Schema::dropIfExists('operator_compliance_items');
    }
};
