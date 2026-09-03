<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use RuntimeException;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * The test suite MUST run against a dedicated testing database. This guard
     * aborts the run before any database work (including RefreshDatabase's
     * wipe/migrate cycle) if the configured connection does not point at a
     * clearly test-only database, so a misconfigured run can never destroy
     * the live `avt-cms` database.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $connection = (string) config('database.default');
        $database = (string) config("database.connections.{$connection}.database");
        $normalised = strtolower($database);

        $isTesting = $database === ':memory:' || str_ends_with($normalised, '_testing') || str_ends_with($normalised, '_test');

        if (! $isTesting) {
            throw new RuntimeException(
                'Refusing to run the test suite: configured connection "'.$connection.'" '
                ."points at database \"{$database}\", which is not a testing database. "
                .'Set DB_DATABASE to a *_testing database (see phpunit.xml) and re-run.'
            );
        }

        return $app;
    }
}
