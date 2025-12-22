<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\InProcessChecksheet;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        InProcessChecksheet::all()->each(function ($checksheet) {
            $dimensions = json_decode($checksheet->dimension_check, true);

            if ($dimensions && is_array($dimensions) && isset($dimensions[0])) {
                $newDimensions = [];
                foreach ($dimensions as $index => $value) {
                    if (!empty($value)) {
                        $newDimensions[floor($index / 3) + 1][($index % 3) + 1] = $value;
                    }
                }
                $checksheet->dimension_check = json_encode($newDimensions);
                $checksheet->save();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        InProcessChecksheet::all()->each(function ($checksheet) {
            $dimensions = json_decode($checksheet->dimension_check, true);

            if ($dimensions && is_array($dimensions) && !isset($dimensions[0])) {
                $oldDimensions = [];
                for ($i = 1; $i <= 8; $i++) {
                    for ($j = 1; $j <= 3; $j++) {
                        $oldDimensions[] = $dimensions[$i][$j] ?? null;
                    }
                }
                $checksheet->dimension_check = json_encode($oldDimensions);
                $checksheet->save();
            }
        });
    }
};
