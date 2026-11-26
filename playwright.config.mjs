import { defineConfig, devices } from '@playwright/test';
import { BASE_URL, E2E_ENV, STUB_URL } from './tests/e2e/env.mjs';

// Run with `bash scripts/e2e.sh` (see README) — it prepares the database and browser first.
export default defineConfig({
    testDir: './tests/e2e',
    // One worker: every test shares one application database.
    workers: 1,
    fullyParallel: false,
    retries: process.env.CI ? 1 : 0,
    forbidOnly: !!process.env.CI,
    timeout: 60_000,
    expect: { timeout: 15_000 },
    outputDir: 'storage/e2e/results',
    reporter: [['list'], ['html', { outputFolder: 'storage/e2e/report', open: 'never' }]],
    use: {
        baseURL: BASE_URL,
        trace: 'retain-on-failure',
        screenshot: 'only-on-failure',
    },
    projects: [
        { name: 'setup', testMatch: /global\.setup\.mjs/ },
        { name: 'chromium', testMatch: /.*\.spec\.mjs/, use: { ...devices['Desktop Chrome'] }, dependencies: ['setup'] },
    ],
    webServer: [
        {
            command: 'node tests/e2e/stub/midtrans-stub.mjs',
            url: `${STUB_URL}/health`,
            reuseExistingServer: false,
        },
        {
            // --no-reload: otherwise `serve` strips most environment variables from the PHP process.
            command: 'php artisan serve --host=127.0.0.1 --port=8011 --no-reload',
            url: `${BASE_URL}/up`,
            env: { ...process.env, ...E2E_ENV },
            reuseExistingServer: false,
            timeout: 90_000,
        },
    ],
});
