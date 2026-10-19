<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('measuring_tools')) {
            Schema::create('measuring_tools', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('plant_id');
                $table->string('name', 100);
                $table->string('serial_number', 100)->nullable();
                $table->string('code', 50)->nullable();
                $table->text('method')->nullable();
                $table->text('description')->nullable();
                $table->json('variables')->nullable();
                $table->integer('order_no')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('plant_id')->references('id')->on('plants')->onDelete('cascade');
                $table->index(['plant_id', 'is_active'], 'idx_mtools_plant_active');
                $table->index(['plant_id', 'name'], 'idx_mtools_plant_name');
            });
        }

        // Auto-seed data alat ukur eksisting ke setiap plant aktif agar konfigurasi sebelumnya tidak hilang
        $plants = DB::table('plants')->where('is_active', 1)->get();
        if ($plants->isNotEmpty()) {
            $defaultTools = ['CALIPER', 'THICKNESS GAUGE', 'FEELER GAUGE', 'PUSH-PULL'];
            $settingRow = DB::table('general_settings')->where('key', 'measuring_tools')->first();
            if ($settingRow && !empty($settingRow->value)) {
                $raw = preg_split('/\r\n|\r|\n|,/', (string)$settingRow->value);
                $clean = array_values(array_unique(array_filter(array_map('trim', $raw))));
                if (!empty($clean)) {
                    $defaultTools = $clean;
                }
            }

            foreach ($plants as $plant) {
                $order = 0;
                foreach ($defaultTools as $toolName) {
                    $upper = strtoupper(trim((string)$toolName));
                    if ($upper === '') continue;

                    $exists = DB::table('measuring_tools')
                        ->where('plant_id', $plant->id)
                        ->where('name', $upper)
                        ->exists();

                    if (!$exists) {
                        DB::table('measuring_tools')->insert([
                            'id' => (string) Str::uuid(),
                            'plant_id' => $plant->id,
                            'name' => $upper,
                            'serial_number' => null,
                            'code' => null,
                            'method' => null,
                            'description' => null,
                            'variables' => null,
                            'order_no' => $order++,
                            'is_active' => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('measuring_tools');
    }
};
