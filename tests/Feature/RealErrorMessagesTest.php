<?php

namespace Tests\Feature;

use App\Support\ErrorMessage;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\MakesWarehouseUsers;
use Tests\TestCase;

/**
 * Client request 2026-09-26: show warehouse staff the real error instead of
 * "Something went wrong". Only for logged-in warehouse users, only while
 * SHOW_REAL_ERRORS is on, never with credentials.
 */
class RealErrorMessagesTest extends TestCase
{
    use MakesWarehouseUsers;

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
}
