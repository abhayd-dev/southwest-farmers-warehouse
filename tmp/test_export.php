<?php

use App\Exports\ProductExport;
use Illuminate\Http\Request;

// Test 1: All products
$export = new ProductExport();
$query = $export->query();
echo "Total products (all): " . $query->count() . "\n";

// Test 2: Filter by search
$filters = ['search' => 'GLASS'];
$export = new ProductExport($filters);
$query = $export->query();
echo "Total products (search: GLASS): " . $query->count() . "\n";
foreach ($query->take(5)->get() as $p) {
    echo " - " . $p->product_name . "\n";
}

// Test 3: Filter by status
$filters = ['status' => '1'];
$export = new ProductExport($filters);
$query = $export->query();
echo "Total products (status: 1): " . $query->count() . "\n";

$filters = ['status' => '0'];
$export = new ProductExport($filters);
$query = $export->query();
echo "Total products (status: 0): " . $query->count() . "\n";
