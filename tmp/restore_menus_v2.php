<?php

use App\Models\AppMenu;
use Illuminate\Support\Facades\DB;

$mapping = [
    // Dashboard (1) - No parent
    
    // Quality Control (2) - No parent
    3 => 2,   // PLANT JAKARTA
    4 => 3,   // MASTER DATA
    5 => 4,   // Data Item
    6 => 4,   // Kategori
    7 => 3,   // ANALYSIS
    8 => 7,   // Sub Assy Anls
    9 => 7,   // Inprocess Anls
    10 => 3,  // CHECKSHEET
    11 => 10, // Sub Assy
    12 => 10, // Inprocess
    13 => 10, // FPA
    14 => 10, // Sortir
    15 => 3,  // LAPORAN
    16 => 15, // Sub Assy
    17 => 15, // Inprocess
    18 => 15, // FPA
    19 => 15, // Sortir
    
    20 => 2,  // PLANT KARAWANG
    21 => 20, // MASTER DATA
    22 => 21, // Data Item
    23 => 21, // Kategori
    24 => 20, // ANALYSIS
    25 => 24, // Sub Assy Anls
    26 => 24, // Inprocess Anls
    27 => 24, // Cross Cut Anls
    28 => 20, // CHECKSHEET
    29 => 28, // Sub Assy
    30 => 28, // Inprocess
    31 => 28, // FPA
    32 => 28, // Cross Cut Plating
    33 => 28, // Cross Cut Painting
    34 => 28, // Sortir
    35 => 28, // Plating
    36 => 28, // Double Tape
    37 => 28, // Incoming Part
    38 => 28, // Incoming Material
    39 => 28, // Incoming Sub-Part
    40 => 28, // Incoming Export
    41 => 28, // Incoming Chemical
    42 => 20, // LAPORAN
    43 => 42, // Sub Assy
    44 => 42, // Inprocess
    45 => 42, // FPA
    46 => 42, // Cross Cut Plating
    47 => 42, // Cross Cut Painting
    48 => 42, // Sortir
    49 => 42, // Plating
    50 => 42, // Double Tape
    51 => 42, // Incoming Part
    52 => 42, // Incoming Material
    53 => 42, // Incoming Sub-Part
    54 => 42, // Incoming Export
    55 => 42, // Incoming Chemical
    
    // Quality Assurance (56) - No parent
    57 => 56, // PLANT JAKARTA
    58 => 57, // List Claim
    59 => 56, // PLANT KARAWANG
    60 => 59, // List Claim
    61 => 56, // Input Ppm dan Total Claim
    
    // Quality System (62) - No parent
    63 => 62, // PLANT JAKARTA
    64 => 63, // KALIBRASI
    65 => 64, // Jadwal Kalibrasi
    66 => 64, // Hasil Verif
    67 => 64, // Daftar Alat
    68 => 63, // KAKOTORA
    69 => 62, // PLANT KARAWANG
    70 => 69, // KALIBRASI
    71 => 70, // Jadwal Kalibrasi
    72 => 70, // Hasil Verif
    73 => 70, // Daftar Alat
    74 => 69, // KAKOTORA
    
    // Pengaturan (75) - No parent
];

DB::transaction(function() use ($mapping) {
    // Step 1: Force all parent_id to NULL
    DB::table('app_menus')->update(['parent_id' => null]);
    
    // Step 2: Apply the correct parent mapping
    foreach ($mapping as $id => $parentId) {
        DB::table('app_menus')->where('id', $id)->update(['parent_id' => $parentId]);
    }
    
    // Step 3: Set order for root items
    $rootItems = [1, 2, 56, 62, 75];
    foreach ($rootItems as $idx => $id) {
        DB::table('app_menus')->where('id', $id)->update(['order' => $idx + 1]);
    }
    
    // Step 4: Auto-order children within each parent
    $parents = DB::table('app_menus')->whereIn('id', array_values($mapping))->orWhereIn('id', $rootItems)->pluck('id');
    foreach ($parents as $parentId) {
        $children = DB::table('app_menus')->where('parent_id', $parentId)->orderBy('id')->get();
        foreach ($children as $idx => $child) {
            DB::table('app_menus')->where('id', $child->id)->update(['order' => $idx + 1]);
        }
    }
});

echo "Menu structure restored successfully v2!";
