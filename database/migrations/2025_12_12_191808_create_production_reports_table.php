<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('production_reports', function (Blueprint $table) {
            $table->id();
            $table->date('report_date');       // Tanggal
            $table->string('product_name');    // Nama Produk
            $table->string('batch_no');        // No Batch
            $table->integer('total_produced'); // Jumlah Produksi
            $table->integer('accepted_qty');   // Jumlah OK
            $table->integer('rejected_qty');   // Jumlah NG (Reject)
            $table->string('inspector_name');  // Nama QC
            $table->text('notes')->nullable(); // Catatan
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('production_reports');
    }
};