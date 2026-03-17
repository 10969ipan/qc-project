<?php

use App\Models\AppMenu;
use Illuminate\Support\Facades\DB;

// Mapping of ID to Parent ID
$mapping = [
    // Quality Control (2)
    3 => 2,   // PLANT JAKARTA
    4 => 3,   // MASTER DATA
    5 => 4, 6 => 4, // Items, Cats
    7 => 3,   // ANALYSIS
    8 => 7, 9 => 7,
    10 => 3,  // CHECKSHEET
    11 => 10, 12 => 10, 13 => 10, 14 => 10,
    15 => 3,  // LAPORAN
    16 => 15, 17 => 15, 18 => 15, 19 => 15,

    20 => 2,  // PLANT KARAWANG
    21 => 20, // MASTER DATA
    22 => 21, 23 => 21,
    24 => 20, // ANALYSIS
    25 => 24, 26 => 24, 27 => 24,
    28 => 20, // CHECKSHEET
    29 => 28, 30 => 28, 31 => 28, 32 => 28, 33 => 28, 34 => 28, 35 => 28, 
    36 => 28, 37 => 28, 38 => 28, 39 => 28, 40 => 28, 41 => 28,
    42 => 20, // LAPORAN
    43 => 42, 44 => 42, 45 => 42, 46 => 42, 47 => 42, 48 => 42, 49 => 42,
    50 => 42, 51 => 42, 52 => 42, 53 => 42, 54 => 42, 55 => 42,

    // Quality Assurance (56)
    57 => 56, // PLANT JAKARTA
    58 => 57, // List Claim
    59 => 56, // PLANT KARAWANG
    60 => 59, // List Claim
    61 => 56, // Input Ppm

    // Quality System (62)
    63 => 62, // PLANT JAKARTA
    64 => 63, // KALIBRASI
    65 => 64, 66 => 64, 67 => 64,
    68 => 63, // KAKOTORA
    69 => 62, // PLANT KARAWANG
    70 => 69, // KALIBRASI
    71 => 70, 72 => 70, 73 => 70,
    74 => 69, // KAKOTORA
];

DB::transaction(function() use ($mapping) {
    // Reset all to NULL first
    AppMenu::query()->update(['parent_id' => null, 'order' => 99]);
    
    // Set specific parents
    foreach ($mapping as $id => $parentId) {
        AppMenu::where('id', $id)->update(['parent_id' => $parentId]);
    }
    
    // Set orders roughly
    $topLevel = [1, 2, 56, 62, 75];
    foreach ($topLevel as $idx => $id) {
        AppMenu::where('id', $id)->update(['order' => $idx + 1]);
    }
    
    // Auto-order children
    $allParents = AppMenu::whereNotNull('parent_id')->distinct()->pluck('parent_id');
    foreach (AppMenu::whereIn('id', $mapping)->orWhereIn('id', $topLevel)->get() as $menu) {
        $children = AppMenu::where('parent_id', $menu->id)->get();
        foreach ($children as $idx => $child) {
            $child->update(['order' => $idx + 1]);
        }
    }
});

echo "Menu structure restored!";
