import { expect, registerUser, test } from './fixtures.mjs';

const COURSE = 'Pengantar Literasi Digital untuk Pendidik';
const SLUG = 'pengantar-literasi-digital-untuk-pendidik';

test('a learner gets a free course, studies a lesson, and passes its quiz', async ({ page }) => {
    await registerUser(page);

    await test.step('add it to the cart and see the button change', async () => {
        await page.goto(`/products/${SLUG}`);
        await page.getByRole('button', { name: 'Enroll Now' }).click();

        await expect(page.getByRole('link', { name: /In your cart/ })).toBeVisible();
        await expect(page.getByRole('button', { name: 'Enroll Now' })).toHaveCount(0);
    });

    await test.step('check out for free', async () => {
        await page.getByRole('link', { name: /In your cart/ }).click();
        await page.getByRole('link', { name: 'Proceed to Checkout' }).click();
        await page.getByRole('button', { name: 'Place Free Order' }).click();

        await expect(page).toHaveURL(/\/orders\/EP-\d{8}-\d{6}$/);
        await expect(page.getByText('Paid').first()).toBeVisible();
    });

    await test.step('find it under My Learning', async () => {
        await page.getByRole('link', { name: 'Go to My Learning' }).click();
        await page.getByRole('link', { name: new RegExp(COURSE) }).click();

        await expect(page.getByRole('progressbar').first()).toHaveAttribute('aria-valuenow', '0');
    });

    await test.step('complete a lesson and move on to the next', async () => {
        await page.getByRole('link', { name: 'Start Course' }).click();
        await page.getByRole('button', { name: 'Mark as complete' }).click();

        await expect(page.getByText('Lesson marked as complete.')).toBeVisible();
        await expect(page.getByRole('progressbar').first()).not.toHaveAttribute('aria-valuenow', '0');

        const firstLesson = page.url();
        await page.getByRole('link', { name: /Next/ }).click();
        await expect(page).not.toHaveURL(firstLesson);
        await expect(page.getByRole('button', { name: 'Mark as complete' })).toBeVisible();
    });

    await test.step('fail the quiz with a half-right answer', async () => {
        await page.goto(`/learning/${SLUG}`);
        await page.getByRole('link', { name: /Quiz: Kuis Akhir/ }).click();

        // The page must not reveal answers before submitting.
        await expect(page.getByText('Why:')).toHaveCount(0);

        await page.getByLabel('Menerapkannya pada contoh kasus').check();
        await page.getByLabel('Belajar sedikit tetapi rutin').check(); // only one of two correct
        await page.getByRole('button', { name: 'Submit answers' }).click();

        await expect(page.getByText('Not passed')).toBeVisible();
        await expect(page.getByText('33%')).toBeVisible();
    });

    await test.step('retake it and pass', async () => {
        await page.getByRole('link', { name: 'Try again' }).click();
        await page.getByLabel('Menerapkannya pada contoh kasus').check();
        await page.getByLabel('Belajar sedikit tetapi rutin').check();
        await page.getByLabel('Mengerjakan latihan secara aktif').check();
        await page.getByLabel('Benar', { exact: true }).check();
        await page.getByRole('button', { name: 'Submit answers' }).click();

        await expect(page.getByText('100%')).toBeVisible();
        await expect(page.getByText('Passed', { exact: true })).toBeVisible();
        await expect(page.getByText('Why:').first()).toBeVisible();
    });

    await test.step('the product page now leads to learning, not buying', async () => {
        await page.goto(`/products/${SLUG}`);
        await expect(page.getByRole('link', { name: /Go to My Learning/ })).toBeVisible();
        await expect(page.getByRole('button', { name: 'Enroll Now' })).toHaveCount(0);
    });
});
