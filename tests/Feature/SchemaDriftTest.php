<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Two apps share one database and each keeps its own copy of the models.
 * Every model must line up with the table this repo's migrations produce.
 */
class SchemaDriftTest extends TestCase
{
    public function test_every_model_matches_the_schema_built_from_migrations(): void
    {
        $this->artisan('schema:drift')->assertExitCode(0);
    }
}
