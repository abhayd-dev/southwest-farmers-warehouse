<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    // Every test gets a freshly migrated schema, so tests never depend on
    // whatever rows happen to exist in a shared database.
    use RefreshDatabase;

    protected function setUp(): void
    {
        // Checked BEFORE the app boots and before RefreshDatabase migrates:
        // migrate:fresh against the real (shared, production) database would
        // wipe it. phpunit.xml forces sqlite; this refuses to run otherwise.
        $connection = $_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION') ?: null;
        if ($connection !== 'sqlite') {
            throw new \RuntimeException(
                'Refusing to run tests: DB_CONNECTION is "' . ($connection ?? 'unset') . '", expected "sqlite". '
                . 'Tests must never touch a real database.'
            );
        }

        parent::setUp();

        // "Permission would be denied" lines are expected while exercising the
        // middleware; keep them out of the test output.
        config(['logging.channels.permissions' => [
            'driver' => 'monolog',
            'handler' => \Monolog\Handler\NullHandler::class,
        ]]);
    }
}
