<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Picqer\Barcode\BarcodeGeneratorPNG;

trait HasBarcodeImage
{
    /**
     * Generate barcode image for the model.
     * Use upc as primary source, then barcode.
     */
    public function generateBarcodeImage()
    {
        // We no longer store barcode images on R2 to ensure maximum speed.
        // Barcodes are now generated on-the-fly in the browser (SVG) or PDF (Base64).
        return null; 
    }
}
