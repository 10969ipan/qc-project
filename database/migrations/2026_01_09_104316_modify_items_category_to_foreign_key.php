<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the existing enum column using raw SQL for MySQL compatibility
        DB::statement('ALTER TABLE items DROP COLUMN category');

        Schema::table('items', function (Blueprint $table) {
            // Add foreign key column
            $table->foreignId('category_id')->nullable()->after('name')->constrained('categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->enum('category', ['Sub Assy', 'Inprosess', 'Cross Cut Plating', 'Cross Cut Painting'])
                ->nullable()
                ->after('name');
        });
    }
};
