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
        Schema::table('machine_statuses', function (Blueprint $table) {
            // Rename 'plant' column to 'plant_id' if it exists
            if (Schema::hasColumn('machine_statuses', 'plant')) {
                $table->renameColumn('plant', 'plant_id');
            }
        });

        Schema::table('machine_statuses', function (Blueprint $table) {
            // Drop the old unique constraint (type, number)
            $table->dropUnique('machine_statuses_type_number_unique');

            // Add new unique constraint including plant_id
            $table->unique(['plant_id', 'type', 'number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machine_statuses', function (Blueprint $table) {
            // Drop the new unique constraint
            $table->dropUnique(['plant_id', 'type', 'number']);

            // Restore old unique constraint
            $table->unique(['type', 'number'], 'machine_statuses_type_number_unique');
        });

        Schema::table('machine_statuses', function (Blueprint $table) {
            // Rename back to 'plant'
            if (Schema::hasColumn('machine_statuses', 'plant_id')) {
                $table->renameColumn('plant_id', 'plant');
            }
        });
    }
};
