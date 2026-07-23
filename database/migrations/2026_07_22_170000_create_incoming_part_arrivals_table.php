<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('incoming_part_arrivals')) {
            Schema::create('incoming_part_arrivals', function (Blueprint $table) {
                $table->id();
                $table->char('plant_id', 36)->nullable();
                $table->char('item_id', 36);
                $table->date('tanggal_datang');
                $table->string('shift_datang');
                $table->integer('qty_datang')->default(0);
                $table->integer('qty_sisa')->default(0);
                $table->string('status')->default('OPEN'); // OPEN / COMPLETED
                $table->timestamps();

                $table->foreign('plant_id')->references('id')->on('plants')->onDelete('set null');
                $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('incoming_parts') && !Schema::hasColumn('incoming_parts', 'arrival_id')) {
            Schema::table('incoming_parts', function (Blueprint $table) {
                $table->unsignedBigInteger('arrival_id')->nullable()->after('item_id');
                $table->foreign('arrival_id')->references('id')->on('incoming_part_arrivals')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('incoming_parts') && Schema::hasColumn('incoming_parts', 'arrival_id')) {
            Schema::table('incoming_parts', function (Blueprint $table) {
                $table->dropForeign(['arrival_id']);
                $table->dropColumn('arrival_id');
            });
        }

        Schema::dropIfExists('incoming_part_arrivals');
    }
};
