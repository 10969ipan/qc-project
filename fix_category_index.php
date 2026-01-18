<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "Fixing Categories Unique Index...\n";

try {
    DB::transaction(function () {
        // Drop the old index if it exists
        // Note: The index name found in schema dump is `categories_name_plant_unique`
        // But we should verify if it exists first? schema dump says yes.

        $table = 'categories';
        $indexName = 'categories_name_plant_unique';

        // Raw SQL is easiest
        DB::statement("DROP INDEX `{$indexName}` ON `{$table}`");
        echo "Dropped old index.\n";

        // Add new index
        DB::statement("CREATE UNIQUE INDEX `{$indexName}` ON `{$table}` (`name`, `plant_id`)");
        echo "Created new composite index on (name, plant_id).\n";
    });
    echo "Success!\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
