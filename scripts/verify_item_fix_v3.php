<?php

use App\Models\User;
use App\Models\Plant;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Http\Requests\StoreItemRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

// Bootstrap Laravel
require dirname(__DIR__) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

DB::beginTransaction();

try {
    // 1. Setup
    $admin = User::where('role', 'admin')->first();
    Auth::login($admin);

    $jakartaPlantId = Plant::where('code', 'jakarta')->value('id');

    // Create two categories for Jakarta
    $cat1 = Category::firstOrCreate(['name' => 'CAT_TEST_1', 'plant_id' => $jakartaPlantId]);
    $cat2 = Category::firstOrCreate(['name' => 'CAT_TEST_2', 'plant_id' => $jakartaPlantId]);

    echo "Using Categories: \n1. {$cat1->name} ({$cat1->id})\n2. {$cat2->name} ({$cat2->id})\n";

    $itemName = "ITEM_UNIQUE_TEST";
    $partNumber = "PN_UNIQUE_TEST";

    // 2. Add item to CAT1
    echo "Creating item in CAT_TEST_1...\n";
    $item1 = Item::create([
        'name' => $itemName,
        'part_number' => $partNumber,
        'category_id' => $cat1->id,
        'plant_id' => $jakartaPlantId
    ]);
    echo "Item 1 Created: ID={$item1->id}, CatID={$item1->category_id}\n";

    // 3. Test StoreItemRequest for CAT2 (should pass)
    echo "Testing StoreItemRequest for CAT_TEST_2 (should succeed)...\n";
    $data2 = [
        'name' => $itemName,
        'part_number' => $partNumber,
        'category_id' => $cat2->id,
        'plant' => 'jakarta',
        'files' => [new \Illuminate\Http\UploadedFile(tempnam(sys_get_temp_dir(), 'test'), 'test.pdf', 'application/pdf', null, true)]
    ];

    // Bind to global request helper
    $request = request();
    $request->merge($data2);

    $storeRequest = new StoreItemRequest();
    $validator2 = Validator::make($data2, $storeRequest->rules());

    if ($validator2->passes()) {
        echo "✅ SUCCESS: Item allowed in different category.\n";
    } else {
        echo "❌ FAILED: Item blocked in different category.\n";
        echo "Errors: " . json_encode($validator2->errors()->all()) . "\n";

        // Manual debug of the query
        $targetPlantId = Plant::resolveId($data2['plant']);
        $query = Item::where('name', $data2['name'])
            ->where('part_number', $data2['part_number'])
            ->where('plant_id', $targetPlantId)
            ->where('category_id', $data2['category_id']);

        echo "SQL: " . $query->toSql() . "\n";
        echo "Bindings: " . json_encode($query->getBindings()) . "\n";
        echo "Exists Count: " . $query->count() . "\n";
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
} finally {
    DB::rollBack();
    echo "Transaction rolled back.\n";
}
