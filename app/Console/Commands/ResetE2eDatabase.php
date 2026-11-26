<?php

namespace App\Console\Commands;

use Database\Seeders\E2eSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetE2eDatabase extends Command
{
    public const DATABASE = 'e2e';

    protected $signature = 'e2e:reset';

    protected $description = 'Wipe and re-seed the end-to-end test database (refuses to touch any other database)';

    public function handle(): int
    {
        // migrate:fresh drops every table, so both guards must hold: if the environment overrides
        // ever failed to apply (e.g. cached config), this stops before the dev database is wiped.
        $database = DB::connection()->getDatabaseName();

        if (! $this->laravel->environment('e2e') || $database !== self::DATABASE) {
            $this->error("Refusing to reset: environment is \"{$this->laravel->environment()}\" and database is \"{$database}\". Expected \"e2e\" / \"".self::DATABASE.'".');

            return self::FAILURE;
        }

        $this->call('migrate:fresh', ['--force' => true, '--seed' => true, '--seeder' => E2eSeeder::class]);

        return self::SUCCESS;
    }
}
