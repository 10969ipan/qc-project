<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('dandory_operator_configs');
        Schema::dropIfExists('dandory_checksheets');
        Schema::dropIfExists('dandory_items');

        // 1. Master item parameter standar Dandory (9 Awal + 10 Akhir)
        // ponytail: separate phase and order_no for simple query & ordering
        Schema::create('dandory_items', function (Blueprint $table) {
            $table->id();
            $table->enum('phase', ['awal', 'akhir'])->index();
            $table->unsignedTinyInteger('order_no');
            $table->string('item_check', 150);
            $table->string('standard', 255);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'phase', 'order_no'], 'idx_dandory_items_phase_order');
        });

        // 2. Data checksheet transaksi Dandory Quality
        // ponytail: store checks as lightweight json maps {"1":"OK","2":"OK"} instead of 19 child rows per transaction
        Schema::create('dandory_checksheets', function (Blueprint $table) {
            $table->id();
            $table->string('plant_code', 20)->default('karawang')->index();
            $table->date('check_date');
            $table->string('shift', 20);
            $table->string('table_no', 50)->index();
            $table->foreignId('operator_id')->constrained('users')->cascadeOnDelete();
            $table->string('jabatan', 100)->nullable();
            $table->char('item_id', 36)->nullable()->index();
            $table->foreign('item_id')->references('id')->on('items')->nullOnDelete();
            $table->string('part_name', 200);
            $table->json('awal_checks')->nullable();
            $table->json('akhir_checks')->nullable();
            $table->timestamp('awal_completed_at')->nullable();
            $table->timestamp('akhir_completed_at')->nullable();
            $table->string('notes', 255)->nullable();
            $table->boolean('leader_checked')->default(false);
            $table->unsignedBigInteger('leader_user_id')->nullable();
            $table->string('leader_name', 100)->nullable();
            $table->timestamp('leader_checked_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['plant_code', 'operator_id', 'check_date', 'shift'], 'idx_dandory_plant_op_date');
            $table->index(['plant_code', 'check_date', 'leader_checked'], 'idx_dandory_plant_date_leader');
            $table->index(['operator_id', 'item_id', 'id'], 'idx_dandory_op_part_preset');
        });

        // 3. Konfigurasi posisi/bagian operator dan kategori part yang diizinkan
        // ponytail: single lightweight mapping table managed by non-inspectors
        Schema::create('dandory_operator_configs', function (Blueprint $table) {
            $table->id();
            $table->string('plant_code', 20)->default('karawang');
            $table->foreignId('operator_id')->constrained('users')->cascadeOnDelete();
            $table->string('bagian', 100)->nullable();
            $table->string('default_table', 50)->nullable();
            $table->json('allowed_categories')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['plant_code', 'operator_id'], 'idx_dandory_op_cfg_plant_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dandory_operator_configs');
        Schema::dropIfExists('dandory_checksheets');
        Schema::dropIfExists('dandory_items');
    }
};
