<?php

namespace Tests\Feature;

use App\Support\ErrorMessage;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\InsertsMinimalRows;
use Tests\Concerns\MakesWarehouseUsers;
use Tests\TestCase;

/**
 * Client request 2026-09-26: show warehouse staff the real error instead of
 * "Something went wrong". Only for logged-in warehouse users, only while
 * SHOW_REAL_ERRORS is on, never with credentials.
 */
class RealErrorMessagesTest extends TestCase
{
    use InsertsMinimalRows, MakesWarehouseUsers;

    protected function setUp(): void
    {
        parent::setUp();
        Route::middleware('web')->get('/__test/boom', fn () => throw new \RuntimeException('Disk full on smtp://apikey:SECRET@smtp.example.com'));
    }

    public function test_staff_see_the_real_error_on_the_500_page(): void
    {
        $this->actingAs($this->superAdmin(), 'warehouse')->get('/__test/boom')
            ->assertStatus(500)
            ->assertSee('Error details')
            ->assertSee('Disk full on smtp://***@smtp.example.com', false)
            ->assertDontSee('SECRET');
    }

    public function test_staff_get_the_real_error_in_json(): void
    {
        $this->actingAs($this->superAdmin(), 'warehouse')->getJson('/__test/boom')
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJson(fn ($json) => $json->where('message', fn ($m) => str_starts_with($m, 'Error: Disk full') && str_contains($m, 'RuntimeException'))->etc());
    }

    public function test_guests_still_get_the_generic_message(): void
    {
        $this->get('/__test/boom')->assertStatus(500)->assertDontSee('Disk full')->assertDontSee('Error details');
        $this->getJson('/__test/boom')->assertJsonPath('message', 'Something went wrong. Please try again later.');
    }

    public function test_switch_off_restores_generic_messages(): void
    {
        config(['app.show_real_errors' => false]);

        $this->actingAs($this->superAdmin(), 'warehouse')->getJson('/__test/boom')
            ->assertJsonPath('message', 'Something went wrong. Please try again later.');
    }

    public function test_specific_context_is_kept_and_sql_connection_details_are_dropped(): void
    {
        $this->actingAs($this->superAdmin(), 'warehouse');
        $e = new \Exception('SQLSTATE[23505]: Unique violation (Connection: pgsql, Host: db.internal, Port: 5432, Database: railway, SQL: insert into "x" ...)');

        $msg = ErrorMessage::from($e, 'Failed to upload invoice document. Please try again.');

        $this->assertStringStartsWith('Failed to upload invoice document. Please try again. Error: SQLSTATE[23505]', $msg);
        $this->assertStringContainsString('SQL: insert into "x"', $msg);
        $this->assertStringNotContainsString('db.internal', $msg);
        $this->assertStringContainsString('tests/Feature/RealErrorMessagesTest.php', $msg, 'points at the app file that failed');
    }

    public function test_a_caught_error_in_a_controller_shows_the_real_reason(): void
    {
        // StaffController::destroy catches and flashes; an unknown id makes it fail.
        $response = $this->actingAs($this->superAdmin(), 'warehouse')
            ->from('/warehouse/staff')
            ->delete(route('warehouse.staff.destroy', 999999));

        $error = (string) session('error');
        $this->assertStringStartsWith('Error: No query results for model [App\\Models\\WareUser] 999999', $error);
        $this->assertStringContainsString('(ModelNotFoundException at app/Http/Controllers/Warehouse/Administration/StaffController.php:', $error);
    }

    public function test_a_business_rule_is_a_plain_warning_not_a_technical_error(): void
    {
        // The stock-adjust screen: removing more than is in stock.
        $this->withoutForeignKeys();
        $productId = $this->insertRow('products', ['product_name' => 'Bell Pepper', 'store_id' => null, 'cost_price' => 10]);
        $this->insertRow('product_stocks', ['product_id' => $productId, 'warehouse_id' => 1, 'quantity' => 676]);

        $this->actingAs($this->superAdmin(), 'warehouse')
            ->from(route('warehouse.stocks.adjust'))
            ->post(route('warehouse.stocks.store-adjustment'), [
                'product_id' => $productId, 'action' => 'subtract', 'reason' => 'damage', 'quantity' => 700,
            ])
            ->assertRedirect(route('warehouse.stocks.adjust'))
            ->assertSessionHas('warning', 'Insufficient stock: only 676 available, cannot remove 700.')
            ->assertSessionMissing('error')
            ->assertSessionHasInput('quantity', 700);
    }

    public function test_business_rules_skip_the_technical_detail_even_with_real_errors_on(): void
    {
        $this->actingAs($this->superAdmin(), 'warehouse');
        $e = new \App\Exceptions\BusinessRuleException('Cannot delete the Main Store Manager.');

        $this->assertSame('Cannot delete the Main Store Manager.', ErrorMessage::from($e, 'Something went wrong.'));
        $this->assertSame(['warning' => 'Cannot delete the Main Store Manager.'], ErrorMessage::flash($e, 'Something went wrong.'));
        $this->assertSame('error', array_key_first(ErrorMessage::flash(new \RuntimeException('x'), 'Something went wrong.')));
    }

    public function test_an_uncaught_business_rule_returns_a_warning_not_a_500(): void
    {
        Route::middleware('web')->get('/__test/rule', fn () => throw new \App\Exceptions\BusinessRuleException('Dispatch quantity cannot exceed pending quantity (5).'));
        $admin = $this->superAdmin();

        $this->actingAs($admin, 'warehouse')->from('/warehouse/stores')->get('/__test/rule')
            ->assertRedirect('/warehouse/stores')
            ->assertSessionHas('warning', 'Dispatch quantity cannot exceed pending quantity (5).');

        $this->actingAs($admin, 'warehouse')->getJson('/__test/rule')
            ->assertStatus(422)
            ->assertJson(['success' => false, 'level' => 'warning', 'message' => 'Dispatch quantity cannot exceed pending quantity (5).']);
    }
}
