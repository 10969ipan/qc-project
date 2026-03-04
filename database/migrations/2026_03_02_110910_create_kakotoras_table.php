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
        Schema::create('kakotoras', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->string('model')->nullable();
            $table->string('part_name')->nullable();
            $table->string('part_number')->nullable();
            $table->string('customer')->nullable();
            $table->string('process')->nullable();
            $table->text('problem')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('countermeasure')->nullable();
            $table->string('status')->nullable()->default('Open');
            $table->string('form_analysis_path')->nullable(); // Column for PICA / AR / SA / dll path
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kakotoras');
    }
};
