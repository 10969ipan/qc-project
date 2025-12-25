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
        Schema::create('cross_cut_checksheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->dateTime('production_datetime');
            $table->dateTime('qc_datetime');
            $table->string('image_path');
            $table->boolean('chemical_copper')->default(false);
            $table->boolean('chemical_nikel')->default(false);
            $table->boolean('chemical_eching')->default(false);
            $table->boolean('chemical_abu')->default(false);
            $table->enum('position_remark_judgment', ['OK', 'NG']);
            $table->string('position_remark_no_lot');
            $table->string('result_remark')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cross_cut_checksheets');
    }
};
