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
        Schema::create('plating_pasang_records', function (Blueprint $table) {
            $table->id();
            $table->text('wip_qrcode');
            $table->string('customer_part');
            $table->string('no_po');
            $table->integer('qty');
            $table->string('lot_id');
            $table->string('unique_code');
            $table->string('sap_code')->nullable();
            $table->date('tanggal_pasang');
            $table->string('shift');
            $table->string('inisial_pasang');
            $table->text('generated_qrcode');
            $table->foreignUuid('plant_id')->constrained('plants')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('plating_cabut_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plating_pasang_record_id')->constrained('plating_pasang_records')->cascadeOnDelete();
            $table->text('pasang_qrcode');
            $table->date('tanggal_cabut');
            $table->string('shift');
            $table->string('no_po');
            $table->string('no_lot_original');
            $table->integer('qty_original');
            $table->foreignUuid('plant_id')->constrained('plants')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('plating_cabut_splits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plating_cabut_record_id')->constrained('plating_cabut_records')->cascadeOnDelete();
            $table->string('no_lot_split');
            $table->integer('qty_split');
            $table->text('generated_qrcode');
            $table->timestamps();
        });

        Schema::table('plating_checksheets', function (Blueprint $table) {
            if (!Schema::hasColumn('plating_checksheets', 'qrcode_verifikasi')) {
                $table->text('qrcode_verifikasi')->nullable()->after('sap_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plating_checksheets', function (Blueprint $table) {
            if (Schema::hasColumn('plating_checksheets', 'qrcode_verifikasi')) {
                $table->dropColumn('qrcode_verifikasi');
            }
        });

        Schema::dropIfExists('plating_cabut_splits');
        Schema::dropIfExists('plating_cabut_records');
        Schema::dropIfExists('plating_pasang_records');
    }
};
