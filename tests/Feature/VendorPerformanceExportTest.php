<?php

namespace Tests\Feature;

use App\Exports\SimpleExport;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Concerns\InsertsMinimalRows;
use Tests\Concerns\MakesWarehouseUsers;
use Tests\TestCase;

class VendorPerformanceExportTest extends TestCase
{
    use InsertsMinimalRows, MakesWarehouseUsers;

    public function test_vendor_performance_lists_completed_po_count_and_spend_per_vendor(): void
    {
        Excel::fake();
        $this->withoutForeignKeys();

        $acme = $this->insertRow('vendors', ['name' => 'Acme Foods']);
        $zed = $this->insertRow('vendors', ['name' => 'Zed Traders']);
        $idle = $this->insertRow('vendors', ['name' => 'Idle Co']);

        foreach ([['vendor' => $acme, 'status' => 'completed', 'total' => 100.5],
                  ['vendor' => $acme, 'status' => 'completed', 'total' => 50],
                  ['vendor' => $acme, 'status' => 'ordered', 'total' => 999],   // not completed: ignored
                  ['vendor' => $zed, 'status' => 'completed', 'total' => 20]] as $po) {
            $this->insertRow('purchase_orders', [
                'vendor_id' => $po['vendor'], 'status' => $po['status'], 'total_amount' => $po['total'],
                'po_number' => 'PO-' . uniqid(), 'order_date' => now()->toDateString(),
            ]);
        }

        $this->actingAs($this->superAdmin())
            ->get(route('warehouse.reports.export', ['report' => 'vendor-performance']))
            ->assertOk();

        Excel::assertDownloaded('vendor_performance_' . date('Y_m-d') . '.xlsx', function (SimpleExport $export) {
            $rows = collect($export->array())->keyBy(0)->map(fn ($row) => array_slice($row, 1));

            $this->assertSame(['Vendor', 'Total Completed POs', 'Total Spend'], $export->headings());
            $this->assertSame([2, '$150.50'], $rows['Acme Foods']);
            $this->assertSame([1, '$20.00'], $rows['Zed Traders']);
            $this->assertSame([0, '$0.00'], $rows['Idle Co']);

            return true;
        });
    }
}
