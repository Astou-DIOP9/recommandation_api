<?php
include 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$all = App\Models\Product::all();
$ids = $all->pluck('id')->toArray();
$counts = array_count_values($ids);
$dups = array_filter($counts, fn($c) => $c > 1);

if ($dups) {
    echo "Duplicate IDs found:" . PHP_EOL;
    foreach ($dups as $id => $count) {
        $products_with_id = App\Models\Product::where('id', $id)->get();
        echo "ID $id appears $count times:" . PHP_EOL;
        foreach ($products_with_id as $p) {
            echo "  - $p->title" . PHP_EOL;
        }
    }
} else {
    echo "All product IDs are unique" . PHP_EOL;
}

echo PHP_EOL . "Total products: " . count($all) . PHP_EOL;
