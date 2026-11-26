import { PASSWORD } from './env.mjs';
import { expect, registerUser, test } from './fixtures.mjs';

test('a visitor can register, log out, and log back in', async ({ page }) => {
    const email = await registerUser(page, 'Rina Pratama');

    await expect(page).toHaveURL(/\/account$/);
    await expect(page.getByRole('heading', { level: 1 })).toContainText('Rina Pratama');

    await page.getByRole('button', { name: 'Logout' }).click();
    await expect(page.getByRole('link', { name: 'Log In' }).first()).toBeVisible();

    await page.goto('/login');
    await page.locator('#email').fill(email);
    await page.locator('#password').fill(PASSWORD);
    await page.getByRole('button', { name: 'Log in' }).click();
    await expect(page).toHaveURL(/\/account$/);
});

test('a wrong password shows a generic error and does not sign in', async ({ page }) => {
    await page.goto('/login');
    await page.locator('#email').fill('nobody@e2e.test');
    await page.locator('#password').fill('not-the-password');
    await page.getByRole('button', { name: 'Log in' }).click();

    await expect(page.getByText('These credentials do not match our records.')).toBeVisible();
    await expect(page).toHaveURL(/\/login$/);
});

test('a guest sent to log in lands where they were heading afterwards', async ({ page }) => {
    const email = await registerUser(page);
    await page.goto('/account');
    await page.getByRole('button', { name: 'Logout' }).click();

    await page.goto('/cart');
    await expect(page).toHaveURL(/\/login$/);

    await page.locator('#email').fill(email);
    await page.locator('#password').fill(PASSWORD);
    await page.getByRole('button', { name: 'Log in' }).click();
    await expect(page).toHaveURL(/\/cart$/);
});
