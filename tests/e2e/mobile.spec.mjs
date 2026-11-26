import { expect, registerUser, test } from './fixtures.mjs';

test.use({ viewport: { width: 375, height: 812 } });

async function expectNoSidewaysScroll(page, path) {
    await page.goto(path);
    const { scroll, client } = await page.evaluate(() => ({
        scroll: document.documentElement.scrollWidth,
        client: document.documentElement.clientWidth,
    }));
    expect(scroll, `${path} scrolls sideways`).toBeLessThanOrEqual(client);
}

test('key pages fit a phone screen', async ({ page }) => {
    for (const path of ['/', '/products', '/products/data-science-fundamentals', '/categories/data-science', '/login', '/register']) {
        await expectNoSidewaysScroll(page, path);
    }

    await registerUser(page);
    await page.goto('/products/pengantar-literasi-digital-untuk-pendidik');
    await page.getByRole('button', { name: 'Enroll Now' }).click();
    await expectNoSidewaysScroll(page, '/cart');
    await expectNoSidewaysScroll(page, '/checkout');
    await page.getByRole('button', { name: 'Place Free Order' }).click();

    for (const path of ['/account', '/account/orders', '/account/learning', '/learning/pengantar-literasi-digital-untuk-pendidik']) {
        await expectNoSidewaysScroll(page, path);
    }
});
