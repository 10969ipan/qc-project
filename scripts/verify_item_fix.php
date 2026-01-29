<?php

use App\Models\User;
use App\Models\Plant;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Http\Requests\StoreItemRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

// Bootstrap Laravel
require dirname(__DIR__) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

DB::beginTransaction();

try {
    // 1. Setup
    $admin = User::where('role', 'admin')->first();
    auth()->login($admin);

    $jakartaPlantId = Plant::where('code', 'jakarta')->value('id');

    // Create two categories for Jakarta
    $cat1 = Category::firstOrCreate(['name' => 'CAT_TEST_1', 'plant_id' => $jakartaPlantId]);
    $cat2 = Category::firstOrCreate(['name' => 'CAT_TEST_2', 'plant_id' => $jakartaPlantId]);

    echo "Using Categories: {$cat1->name} ({$cat1->id}) and {$cat2->name} ({$cat2->id})\n";

    $itemName = "ITEM_UNIQUE_TEST";
    $partNumber = "PN_UNIQUE_TEST";

    // 2. Add item to CAT1
    echo "Creating item in CAT_TEST_1...\n";
    Item::create([
        'name' => $itemName,
        'part_number' => $partNumber,
        'category_id' => $cat1->id,
        'plant_id' => $jakartaPlantId
    ]);

    // 3. Test StoreItemRequest for CAT2 (should pass)
    echo "Testing StoreItemRequest for CAT_TEST_2 (should succeed)...\n";
    $data2 = [
        'name' => $itemName,
        'part_number' => $partNumber,
        'category_id' => $cat2->id,
        'plant' => 'jakarta'
    ];

    $request2 = new StoreItemRequest();
    $request2->merge($data2);
    $request2->setUserResolver(function () use ($admin) {
        return $admin; });

    $validator2 = Validator::make($data2, $request2->rules());
    if ($validator2->passes()) {
        echo "✅ SUCCESS: Item allowed in different category.\n";
    } else {
        echo "❌ FAILED: Item blocked in different category.\n";
        echo "Errors: " . json_encode($validator2->errors()->all()) . "\n";
    }

    // 4. Test StoreItemRequest for CAT1 again (should fail)
    echo "Testing StoreItemRequest for CAT_TEST_1 again (should fail)...\n";
    $dataDuplicate = [
        'name' => $itemName,
        'part_number' => $partNumber,
        'category_id' => $cat1->id,
        'plant' => 'jakarta'
    ];

    $requestDuplicate = new StoreItemRequest();
    $requestDuplicate->merge($dataDuplicate);
    $requestDuplicate->setUserResolver(function () use ($admin) {
        return $admin; });

    $validatorDuplicate = Validator::make($dataDuplicate, $requestDuplicate->rules());
    if ($validatorDuplicate->fails()) {
        echo "✅ SUCCESS: Validation correctly blocked duplicate in same category.\n";
        echo "Error Message: " . $validatorDuplicate->errors()->first('name') . "\n";
    } else {
        echo "❌ FAILED: Validation failed to block duplicate in same category.\n";
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
} finally {
    DB::rollBack();
    echo "Transaction rolled back.\n";
}
