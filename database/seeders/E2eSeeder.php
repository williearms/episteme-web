<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Demo catalog plus a fixed admin for the browser tests. The credentials are throwaway test
 * values, so this refuses to run anywhere but the e2e environment.
 */
class E2eSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('e2e')) {
            throw new RuntimeException('E2eSeeder may only run with APP_ENV=e2e.');
        }

        $this->call(DatabaseSeeder::class);

        User::factory()->admin()->create([
            'name' => 'E2E Admin',
            'email' => 'admin@e2e.test',
            'password' => 'e2e-password-123',
        ]);
    }
}
