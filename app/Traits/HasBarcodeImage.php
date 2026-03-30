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
        $code = $this->upc ?: $this->barcode;
        
        if (!$code) {
            return null;
        }

        try {
            $generator = new BarcodeGeneratorPNG();
            // Use CODE_128 format which is widely used for alphanumeric
            $barcodeData = $generator->getBarcode((string)$code, $generator::TYPE_CODE_128);
            
            $directory = 'barcodes';
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            $filename = $directory . '/' . $code . '.png';
            Storage::disk('public')->put($filename, $barcodeData);
            
            $this->update(['barcode_image' => $filename]);
            
            return $filename;
        } catch (\Exception $e) {
            \Log::error('Barcode generation failed: ' . $e->getMessage());
            return null;
        }
    }
}
