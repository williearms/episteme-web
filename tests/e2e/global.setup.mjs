import { execFileSync } from 'node:child_process';
import { expect, test as setup } from '@playwright/test';
import { ADMIN_EMAIL, ADMIN_STATE, E2E_ENV, PASSWORD } from './env.mjs';


setup('reset the e2e database', () => {
    // e2e:reset refuses to run unless the environment *and* database name are "e2e".
    execFileSync('php', ['artisan', 'e2e:reset'], { stdio: 'inherit', env: { ...process.env, ...E2E_ENV } });
});

// Log the admin in once and reuse the session: logging in per test would trip the login rate limit.
setup('sign in as the admin', async ({ page }) => {
    await page.goto('/login');
    await page.getByLabel('Email').first().fill(ADMIN_EMAIL);
    await page.getByLabel('Password', { exact: false }).first().fill(PASSWORD);
    await page.getByRole('button', { name: 'Log in' }).click();
    await expect(page).toHaveURL(/\/account$/);
    await page.context().storageState({ path: ADMIN_STATE });
});
