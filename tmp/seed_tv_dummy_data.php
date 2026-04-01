<?php

use App\Models\Plant;
use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$plant = Plant::where('name', 'Karawang')->first();
if (!$plant) {
    echo "Karawang plant not found.\n";
    return;
}

$user = User::where('plant_id', $plant->id)->first() ?? User::where('role', 'admin')->first() ?? User::first();
$items = Item::where('plant_id', $plant->id)->limit(10)->get();

if ($items->isEmpty()) {
    echo "No items found for Karawang plant.\n";
    return;
}

echo "Seeding data for Plant: {$plant->name} (ID: {$plant->id})\n";

// Seed Sub-Assy (Meja 1-15)
foreach (range(1, 15) as $i) {
    if (rand(0, 100) > 20) { // 80% chance of being active
        $item = $items->random();
        DB::table('sub_assy_checksheets')->insert([
            'plant_id' => $plant->id,
            'user_id' => $user->id,
            'item_id' => $item->id,
            'line' => $i,
            'judgment' => rand(0, 100) > 10 ? 'OK' : 'NG',
            'operator_initials' => $user->initials ?: 'ADM',
            'date' => Carbon::today()->toDateString(),
            'shift' => 1,
            'created_at' => Carbon::now()->subMinutes(rand(1, 120)),
            'updated_at' => Carbon::now(),
            'approval_status' => 'pending',
            'total_qty' => 100,
            'sampling_qty' => 10,
            'total_ok' => 9,
            'total_ng' => 1,
        ]);
    }
}

// Seed In-Process (Mesin 1-24)
foreach (range(1, 24) as $i) {
    if (rand(0, 100) > 15) { // 85% chance of being active
        $item = $items->random();
        DB::table('in_process_checksheets')->insert([
            'plant_id' => $plant->id,
            'user_id' => $user->id,
            'item_id' => $item->id,
            'code_machine' => (string)$i,
            'judgment' => rand(0, 100) > 10 ? 'OK' : 'NG',
            'operator_initials' => $user->initials ?: 'ADM',
            'date' => Carbon::today()->toDateString(),
            'shift' => 1,
            'created_at' => Carbon::now()->subMinutes(rand(1, 120)),
            'updated_at' => Carbon::now(),
            'approval_status' => 'pending',
            'total_qty' => 1000,
            'sampling_qty' => 50,
            'total_ok' => 45,
            'total_ng' => 5,
        ]);
    }
}

echo "Seeded Sub-Assy and In-Process dummy data successfully.\n";
