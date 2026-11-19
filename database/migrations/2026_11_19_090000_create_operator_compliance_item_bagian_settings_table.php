<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('operator_compliance_item_bagian_settings')) {
            return;
        }

        Schema::create('operator_compliance_item_bagian_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->string('bagian', 100);
            $table->boolean('is_enabled')->default(true);
            $table->string('custom_prinsip_dasar')->nullable();
            $table->string('custom_item_check')->nullable();
            $table->text('custom_standard')->nullable();
            $table->integer('custom_order_no')->nullable();
            $table->timestamps();

            $table->unique(['item_id', 'bagian'], 'uq_op_comp_item_bagian');
            $table->index(['bagian', 'is_enabled'], 'idx_op_comp_bagian_enabled');
            $table->foreign('item_id', 'fk_op_comp_ibs_item')
                ->references('id')
                ->on('operator_compliance_items')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operator_compliance_item_bagian_settings');
    }
};
