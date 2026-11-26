import { expect, registerUser, test } from './fixtures.mjs';

test('guests are sent to log in before any account page', async ({ page }) => {
    for (const path of ['/account', '/account/orders', '/account/learning', '/cart', '/checkout']) {
        await page.goto(path);
        await expect(page).toHaveURL(/\/login$/);
    }
});

test('a regular user cannot reach the admin panel or content they have not bought', async ({ page }) => {
    await registerUser(page);

    const admin = await page.goto('/admin');
    expect(admin.status()).toBe(403);
    await expect(page.getByText('have access to this page')).toBeVisible();

    const learning = await page.goto('/learning/data-science-fundamentals');
    expect(learning.status()).toBe(404);
    await expect(page.getByRole('heading', { name: 'Page not found' })).toBeVisible();
});

test('one customer cannot open another customer order', async ({ page, browser }) => {
    await registerUser(page, 'Owner');
    await page.goto('/products/pengantar-literasi-digital-untuk-pendidik');
    await page.getByRole('button', { name: 'Enroll Now' }).click();
    await page.goto('/checkout');
    await page.getByRole('button', { name: 'Place Free Order' }).click();
    await expect(page).toHaveURL(/\/orders\/EP-/);
    const orderUrl = page.url();

    const intruderContext = await browser.newContext();
    const intruder = await intruderContext.newPage();
    await registerUser(intruder, 'Intruder');
    const response = await intruder.goto(orderUrl);

    expect(response.status()).toBe(403);
    await intruderContext.close();
});
