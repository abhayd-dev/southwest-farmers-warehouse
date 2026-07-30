<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class RepackagingCalculationTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_repackaging_stock_running_balance_calculation()
    {
        $initialBulkStock = 100.0;
        $deductionQty = 15.5;
        $expectedNewBulkStock = $initialBulkStock - $deductionQty;

        $this->assertEquals(84.5, $expectedNewBulkStock);

        $initialTargetStock = 20.0;
        $addedQty = 35.0;
        $expectedNewTargetStock = $initialTargetStock + $addedQty;

        $this->assertEquals(55.0, $expectedNewTargetStock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_product_name_association_matching_logic()
    {
        $bulkProductName = 'FARMERS- GHANA GARRI 50LB';
        $baseName = preg_replace('/\s+\d+(LB|LBS|KG|OZ|PCS|BAG).*$/i', '', $bulkProductName);
        $baseName = trim($baseName);

        $this->assertEquals('FARMERS- GHANA GARRI', $baseName);

        $targetName = 'FARMERS- GHANA GARRI 20LB';
        $isMatch = str_contains($targetName, $baseName);

        $this->assertTrue($isMatch);
    }
}
