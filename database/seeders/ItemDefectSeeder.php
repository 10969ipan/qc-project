<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemDefectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $itemsData = [
            [
                'name' => 'COVER, ASSY FUEL TANK',
                'defects' => [
                    'FLASH',
                    'RUBER FUEL SEAL TIDAK TERPASANG',
                    'TUBE DRAIN TIDAK TERPASANG',
                    'LABEL FUEL TIDAK TERPASANG',
                    'BEDA WARNA',
                    'FLASH HOLE'
                ]
            ],
            [
                'name' => 'COVER, FR. TOP SET',
                'defects' => [
                    'SINK MARK',
                    'FLASH PARTING LINE',
                    'FLOW',
                    'ALL LOCKING SHORT MOULD',
                    'BARET',
                    'BEDA WARNA / SHINING',
                    'HOLE FLASH',
                    'NUT CLIP TERBALIK',
                    'RIB SHORT'
                ]
            ],
            [
                'name' => 'COVER HANDLE RR ASSY',
                'defects' => [
                    'NUT SPRING 4MM TIDAK TERPASANG',
                    'FLASH',
                    'SINKMARK',
                    'KASAR',
                    'KONTAMINASI',
                    'BEDA WARNA',
                    'UNDER CUT',
                    'GOMPAL',
                    'WELD LINE',
                    'FLOW'
                ]
            ]
        ];

        foreach ($itemsData as $data) {
            $item = Item::where('name', 'like', $data['name'] . '%')->first();

            if ($item) {
                $item->update(['defects' => $data['defects']]);
                $this->command->info('Updated defects for: ' . $item->name);
            } else {
                // Check if we can create it. We need 'file_path' usually.
                // If it's nullable in DB but required in Controller, we might get away with it here if DB allows.
                // However, without a file, the system might not work well.
                // Let's try to create it with a placeholder or null if allowed.
                // Looking at ItemController, file_path is required logic, but let's see Migration.
                // I don't see migration, but I can try.
                
                try {
                     // Attempt to create if not exists
                     Item::create([
                        'name' => $data['name'],
                        'defects' => $data['defects'],
                        // 'file_path' => '', // Assuming nullable or we can update later
                        // 'customer' => '',
                        // 'part_number' => ''
                     ]);
                     $this->command->info('Created item: ' . $data['name']);
                } catch (\Exception $e) {
                    $this->command->error('Failed to create item ' . $data['name'] . ': ' . $e->getMessage());
                }
            }
        }
    }
}
