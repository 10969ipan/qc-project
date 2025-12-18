<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checksheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->date('date');
            $table->string('shift');
            $table->integer('total_qty');
            $table->integer('sampling_qty');
            $table->integer('total_ok');
            $table->integer('total_ng');
            $table->string('judgment'); // OK / NG
            $table->text('remarks')->nullable();
            $table->json('defects')->nullable(); // Store defects as JSON array
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checksheets');
    }
};
