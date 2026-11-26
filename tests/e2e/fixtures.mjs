import { createHash, randomUUID } from 'node:crypto';
import { expect, test as base } from '@playwright/test';
import { PASSWORD, SERVER_KEY } from './env.mjs';

/**
 * Upstream Filament 3.3.55 bug, only on the admin panel: when a modal containing a RichEditor
 * closes, its state watcher still runs and writes to a ref that has already been removed. It fires
 * after the save has happened and affects nothing. Matched on message *and* source file, so any
 * other error (including a different one from the editor) still fails the test.
 */
function isKnownUpstreamError(report) {
    return report.includes("Cannot set properties of undefined (setting 'value')")
        && report.includes('/js/filament/forms/components/rich-editor.js');
}

/** Every test also fails if the page throws an uncaught JavaScript error. */
export const test = base.extend({
    page: async ({ page }, use) => {
        const errors = [];
        page.on('pageerror', (error) => {
            const report = error.stack || error.message;

            if (!isKnownUpstreamError(report)) {
                errors.push(report);
            }
        });
        await use(page);
        expect(errors, 'uncaught JavaScript errors').toEqual([]);
    },
});

export { expect };

/** Register through the real form, leaving the browser signed in as that new user. */
export async function registerUser(page, name = 'E2E Learner') {
    const email = `learner-${randomUUID()}@e2e.test`;

    await page.goto('/register');
    await page.getByLabel('Name').fill(name);
    await page.getByLabel('Email', { exact: false }).first().fill(email);
    await page.locator('#password').fill(PASSWORD);
    await page.locator('#password_confirmation').fill(PASSWORD);
    await page.getByRole('button', { name: 'Create account' }).click();
    await expect(page.getByRole('link', { name: 'Cart' }).first()).toBeVisible();

    return email;
}

/** The signature Midtrans puts on its notifications. */
export function signedNotification(orderId, grossAmount, transactionStatus, key = SERVER_KEY) {
    const statusCode = '200';
    const amount = `${grossAmount}.00`;

    return {
        order_id: orderId,
        status_code: statusCode,
        gross_amount: amount,
        transaction_status: transactionStatus,
        signature_key: createHash('sha512').update(orderId + statusCode + amount + key).digest('hex'),
    };
}
