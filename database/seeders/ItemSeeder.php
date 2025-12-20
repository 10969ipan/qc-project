<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample Data for Items
        // Format: Name, Part Number, Customer
        $basepathyiim = 'master item/yimm/';
        $basepathahm = 'master item/ahm/';
        $items = [
            [
                'name' => 'COVER, FR. TOP SET',
                'part_number' => '6430B-K2V -N800',
                'customer' => 'PT. ASTRA HONDA MOTOR',
                'file_path' => $basepathahm . '0101. PCCP Cover, Front Top Set (6430B-K2V -N800) (Outgoing Subassy).pdf', // Placeholder or path if available
            ],
            [
                'name' => 'COVER HANDLE RR ASSY',
                'part_number' => '5320C-K3V -N000-DL',
                'customer' => 'PT. ASTRA HONDA MOTOR',
                'file_path' => $basepathahm . '098. PCCP Cover, Handle RR Assy (53206-K3V-N000) (Outgoing Subassy).pdf', // Placeholder or path if available
            ],
            [
                'name' => 'COVER, ASSY FUEL TANK',
                'part_number' => '1757A-K0JJ-NA00',
                'customer' => 'PT. ASTRA HONDA MOTOR',
                'file_path' => $basepathahm . '0103. PCCP Cover, Assy Fuel Tank (1757A-K0JJ-NA00) (Outgoing Subassy).pdf', // Placeholder or path if available
            ],
            [
                'name' => 'EMBLEM 3D',
                'part_number' => '1PA - F836B - 00',
                'customer' => 'PT. YAMAHA INDONESIA MOTOR MFG',
                'file_path' => $basepathyiim . '-.pdf', // Placeholder or path if available
            ],
            [
                'name' => 'COVER HNDL END K3VA',
                'part_number' => '53102-K0L -D002',
                'customer' => 'PT. ASTRA HONDA MOTOR',
                'file_path' => $basepathahm . '-.pdf', // Placeholder or path if available
            ],
            [
                'name' => 'COVER HEAD LIGHT  (NATURAL)',
                'part_number' => '53209-K3V-N100',
                'customer' => 'PT. ASTRA HONDA MOTOR',
                'file_path' => $basepathahm . '-.pdf', // Placeholder or path if available
            ],
        ];

        foreach ($items as $item) {
            Item::create($item);
        }
    }
}
