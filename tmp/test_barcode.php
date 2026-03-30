<?php

use App\Models\Product;
use Illuminate\Support\Facades\Storage;

// This script will find a product with a UPC and try to generate its barcode image.
$product = Product::whereNotNull('upc')->first();

if ($product) {
    echo "Found product: " . $product->product_name . " (UPC: " . $product->upc . ")\n";
    $path = $product->generateBarcodeImage();
    if ($path) {
        echo "Barcode image generated: " . $path . "\n";
        if (Storage::disk('public')->exists($path)) {
            echo "Success: File exists in storage.\n";
        } else {
            echo "Error: File DOES NOT exist in storage.\n";
        }
    } else {
        echo "Error: generateBarcodeImage returned null.\n";
    }
} else {
    echo "No product with UPC found. Trying with barcode.\n";
    $product = Product::whereNotNull('barcode')->first();
    if ($product) {
        echo "Found product: " . $product->product_name . " (Barcode: " . $product->barcode . ")\n";
        $path = $product->generateBarcodeImage();
        if ($path) {
            echo "Barcode image generated: " . $path . "\n";
            if (Storage::disk('public')->exists($path)) {
                echo "Success: File exists in storage.\n";
            } else {
                echo "Error: File DOES NOT exist in storage.\n";
            }
        } else {
            echo "Error: generateBarcodeImage returned null.\n";
        }
    } else {
        echo "No product found to test.\n";
    }
}
