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
        Schema::table('kakotoras', function (Blueprint $table) {
            $table->text('similar_part')->nullable()->change();
            $table->text('cause')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kakotoras', function (Blueprint $table) {
            $table->string('similar_part', 255)->nullable()->change();
            $table->string('cause', 255)->nullable()->change();
        });
    }
};
