<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NextProcess;
use App\Models\Plant;

class NextProcessSeeder extends Seeder
{
    public function run()
    {
        $jakartaId = Plant::where('name', 'Jakarta')->first()?->id;
        $karawangId = Plant::where('name', 'Karawang')->first()?->id;

        if (!$jakartaId || !$karawangId) {
            $this->command->error('Plant Jakarta or Karawang not found!');
            return;
        }

        $modules = [
            'plating', 'sub_assy', 'sortir', 'in_process', 
            'first_piece_approval', 'double_tape', 'cross_cut', 'cross_cut_painting'
        ];

        // Standard processes for most modules and Karawang
        $standardProcesses = [
            ['name' => 'CRUSHING', 'order' => 1],
            ['name' => 'SORTIR', 'order' => 2],
            ['name' => 'FINISHING', 'order' => 3],
            ['name' => 'REPAIR', 'order' => 4],
            ['name' => 'MARKING+FINISHING+PACKING', 'order' => 5],
        ];

        // Complex processes for Jakarta In-Process & FPA
        $complexProcesses = [
            ['name' => 'CRUSHING', 'order' => 1],
            ['name' => 'SORTIR', 'order' => 2],
            ['name' => 'FINISHING', 'order' => 3],
            ['name' => 'REPAIR', 'order' => 4],
            ['name' => 'SORTIR + FINISHING', 'order' => 5],
            ['name' => 'FINISHING + PASANG SUB PART', 'order' => 6],
            ['name' => 'FINISHING + PACKING', 'order' => 7],
            ['name' => 'REBUS + FINISHING + PACKING', 'order' => 8],
            ['name' => 'MARKING+FINISHING+PACKING', 'order' => 9],
            ['name' => 'SORTIR + CRUSHING', 'order' => 10],
            ['name' => 'FINISHING + MARKING + PACKING', 'order' => 11],
        ];

        foreach ($modules as $module) {
            // Seed Karawang (all modules use standard)
            foreach ($standardProcesses as $proc) {
                NextProcess::updateOrCreate(
                    ['plant_id' => $karawangId, 'module' => $module, 'name' => $proc['name']],
                    ['order' => $proc['order'], 'is_active' => true]
                );
            }

            // Seed Jakarta
            if (in_array($module, ['in_process', 'first_piece_approval'])) {
                // Jakarta In-Process & FPA use complex list
                foreach ($complexProcesses as $proc) {
                    NextProcess::updateOrCreate(
                        ['plant_id' => $jakartaId, 'module' => $module, 'name' => $proc['name']],
                        ['order' => $proc['order'], 'is_active' => true]
                    );
                }
            } else {
                // Other Jakarta modules use standard list
                foreach ($standardProcesses as $proc) {
                    NextProcess::updateOrCreate(
                        ['plant_id' => $jakartaId, 'module' => $module, 'name' => $proc['name']],
                        ['order' => $proc['order'], 'is_active' => true]
                    );
                }
            }
        }
    }
}
