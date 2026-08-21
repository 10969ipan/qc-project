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
        Schema::create('incoming_part_arrival_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('arrival_id')->nullable()->constrained('incoming_part_arrivals')->onDelete('set null');
            $table->string('plant_id', 50)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('user_name')->nullable();
            $table->string('item_name')->nullable();
            $table->string('part_number')->nullable();
            $table->date('tanggal_datang')->nullable();
            $table->string('shift_datang')->nullable();
            $table->string('action_type'); // 'IN', 'OUT', 'UPDATE', 'DELETE'
            $table->integer('qty_before')->default(0);
            $table->integer('qty_change')->default(0);
            $table->integer('qty_after')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incoming_part_arrival_logs');
    }
};
