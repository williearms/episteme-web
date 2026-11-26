import { expect, registerUser, test } from './fixtures.mjs';
import { ADMIN_STATE } from './env.mjs';

test.use({ storageState: ADMIN_STATE });

/** Filament's searchable select (Choices.js): open it, then click the option. */
async function chooseSearchable(page, fieldLabel, option) {
    const field = page.locator('.fi-fo-field-wrp').filter({ hasText: new RegExp(`^\\s*${fieldLabel}`) }).first();
    await field.locator('.choices').click();
    await field.locator('.choices__list--dropdown .choices__item--selectable', { hasText: option }).first().click();
}

test('an admin publishes a webinar and it appears in the public catalog without its join link', async ({ page, browser }) => {
    const title = 'Webinar E2E Literasi Data';
    const joinUrl = 'https://meet.example.com/e2e-secret-room';

    await page.goto('/admin/products/create');
    await chooseSearchable(page, 'Category', 'Data Science');

    // Filament marks required fields with a "*" that is part of the accessible name.
    const field = (name) => page.getByRole('textbox', { name: new RegExp(`^${name}\\*?$`) });

    await field('Title').fill(title);
    await field('Title').blur();
    await expect(field('Slug')).toHaveValue('webinar-e2e-literasi-data');
    await field('Short description').fill('Dibuat oleh tes end-to-end.');
    await field('Description').fill('Deskripsi lengkap webinar uji.');
    await page.getByRole('combobox', { name: /^Product type\*?$/ }).selectOption('webinar');
    await page.getByLabel('Price').fill('0');
    await page.getByRole('combobox', { name: /^Status\*?$/ }).selectOption('published');
    await field('Platform').fill('Zoom');
    await field('Join url').fill(joinUrl);
    await page.getByRole('button', { name: 'Create', exact: true }).click();

    await expect(page).toHaveURL(/\/admin\/products\/webinar-e2e-literasi-data\/edit$/);

    const visitorContext = await browser.newContext({ storageState: { cookies: [], origins: [] } });
    const visitor = await visitorContext.newPage();
    await visitor.goto('/products/webinar-e2e-literasi-data');
    await expect(visitor.getByRole('heading', { level: 1, name: title })).toBeVisible();
    expect(await visitor.content()).not.toContain('e2e-secret-room');
    await visitorContext.close();
});

test('lesson content written in the admin editor reaches the student, formatted', async ({ page, browser }) => {
    await page.goto('/admin/course-modules');
    await page.getByPlaceholder('Search').fill('Literasi Digital');
    const row = page.locator('table tbody tr').filter({ hasText: 'Pengantar dan Konsep Dasar' });
    await expect(row).toHaveCount(1);
    await row.getByRole('link', { name: 'Edit' }).click();

    const lessonRow = page.locator('table tbody tr').filter({ hasText: 'Materi 1.1' });
    await lessonRow.getByRole('button', { name: 'Edit' }).click();

    const editor = page.getByRole('dialog').locator('trix-editor');
    await editor.click();
    await page.keyboard.press('Control+A');
    await page.keyboard.press('Delete');
    await page.keyboard.type('Ringkasan ');
    await page.keyboard.press('Control+B');
    await page.keyboard.type('ditulis admin');
    await page.keyboard.press('Control+B');
    await page.getByRole('dialog').getByRole('button', { name: 'Save changes' }).click();
    await expect(page.getByRole('dialog')).toBeHidden();

    const studentContext = await browser.newContext({ storageState: { cookies: [], origins: [] } });
    const student = await studentContext.newPage();
    await registerUser(student, 'Murid');
    await student.goto('/products/pengantar-literasi-digital-untuk-pendidik');
    await student.getByRole('button', { name: 'Enroll Now' }).click();
    await student.goto('/checkout');
    await student.getByRole('button', { name: 'Place Free Order' }).click();
    await student.goto('/learning/pengantar-literasi-digital-untuk-pendidik');
    await student.getByRole('link', { name: 'Start Course' }).click();

    await expect(student.locator('.lesson-content')).toContainText('Ringkasan ditulis admin');
    await expect(student.locator('.lesson-content strong')).toHaveText('ditulis admin');
    await studentContext.close();
});
