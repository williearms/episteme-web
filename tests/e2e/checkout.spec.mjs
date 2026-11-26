import { STUB_URL } from './env.mjs';
import { expect, registerUser, signedNotification, test } from './fixtures.mjs';

const COURSE = 'Data Science Fundamentals';
const PRICE = 299000;
const WEBHOOK = '/payments/webhook/midtrans';

/** Buy the paid course up to the (fake) payment page; returns the per-attempt reference and order number. */
async function checkOutToPaymentPage(page) {
    await page.goto('/products/data-science-fundamentals');
    await page.getByRole('button', { name: 'Enroll Now' }).click();
    await page.getByRole('link', { name: /In your cart/ }).click();
    await page.getByRole('link', { name: 'Proceed to Checkout' }).click();
    await expect(page.getByText('Rp299.000').first()).toBeVisible();
    await page.getByRole('button', { name: 'Place Order & Pay' }).click();

    await expect(page).toHaveURL(new RegExp(`^${STUB_URL}/pay/`));
    await expect(page.getByRole('heading', { name: 'Fake payment page' })).toBeVisible();
    const reference = await page.locator('#order').innerText();

    return { reference, orderNumber: reference.replace(/-P\d+$/, '') };
}

test('a paid purchase unlocks access only after a genuine payment notification', async ({ page, request }) => {
    await registerUser(page);
    const { reference, orderNumber } = await checkOutToPaymentPage(page);

    await test.step('the gateway was sent the server-side price', async () => {
        const sent = await (await request.get(`${STUB_URL}/transactions`)).json();
        const mine = sent.find((t) => t.transaction_details.order_id === reference);
        expect(mine.transaction_details.gross_amount).toBe(PRICE);
    });

    await test.step('the order is pending and grants nothing yet', async () => {
        await page.goto(`/orders/${orderNumber}`);
        await expect(page.getByText('Pending Payment')).toBeVisible();
        await page.goto('/account/learning');
        await expect(page.getByText(/enrolled in anything yet/)).toBeVisible();
    });

    await test.step('forged and tampered notifications are refused', async () => {
        const forged = await request.post(WEBHOOK, { data: signedNotification(reference, PRICE, 'settlement', 'wrong-key') });
        expect(forged.status()).toBe(403);

        const underpaid = await request.post(WEBHOOK, { data: signedNotification(reference, 1000, 'settlement') });
        expect(underpaid.status()).toBe(422);

        await page.goto(`/orders/${orderNumber}`);
        await expect(page.getByText('Pending Payment')).toBeVisible();
    });

    await test.step('the genuine notification (even delivered twice) settles it', async () => {
        const notification = signedNotification(reference, PRICE, 'settlement');
        expect((await request.post(WEBHOOK, { data: notification })).status()).toBe(200);
        expect((await request.post(WEBHOOK, { data: notification })).status()).toBe(200);

        await page.goto(`/orders/${orderNumber}`);
        await expect(page.getByText('Pending Payment')).toHaveCount(0);
        await expect(page.getByText('Paid').first()).toBeVisible();
    });

    await test.step('the course is now in My Learning, once', async () => {
        await page.getByRole('link', { name: 'Go to My Learning' }).click();
        await expect(page.getByRole('link', { name: new RegExp(COURSE) })).toHaveCount(1);
    });

    await test.step('the order is listed under My Orders', async () => {
        await page.goto('/account/orders');
        await expect(page.getByRole('link', { name: new RegExp(orderNumber) })).toBeVisible();
    });
});

test('a declined payment can be retried as a new attempt', async ({ page, request }) => {
    await registerUser(page);
    const first = await checkOutToPaymentPage(page);

    expect((await request.post(WEBHOOK, { data: signedNotification(first.reference, PRICE, 'deny') })).status()).toBe(200);

    await page.goto(`/orders/${first.orderNumber}`);
    await expect(page.getByText('Failed', { exact: true })).toBeVisible();
    await page.getByRole('button', { name: 'Retry Payment' }).click();

    await expect(page).toHaveURL(new RegExp(`^${STUB_URL}/pay/`));
    const second = await page.locator('#order').innerText();
    expect(second).not.toBe(first.reference);
    expect(second.replace(/-P\d+$/, '')).toBe(first.orderNumber);

    expect((await request.post(WEBHOOK, { data: signedNotification(second, PRICE, 'settlement') })).status()).toBe(200);
    await page.goto(`/orders/${first.orderNumber}`);
    await page.getByRole('link', { name: 'Go to My Learning' }).click();
    // The button alone only proves the order is paid; check the access was actually granted.
    await expect(page.getByRole('link', { name: new RegExp(COURSE) })).toHaveCount(1);
});
