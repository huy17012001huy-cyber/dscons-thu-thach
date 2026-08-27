import { expect, test } from '@playwright/test';

const protectedPaths = [
    '/feed',
    '/cot',
    '/tin-hieu',
    '/hoi-dap',
    '/challenge',
    '/leaderboard',
    '/khoa-hoc',
    '/su-kien',
    '/marketplace',
    '/affiliate',
    '/messages',
    '/search',
];

test.beforeEach(async ({ context }) => {
    const sessionCookie = process.env.QA_SESSION_COOKIE;
    test.skip(!sessionCookie, 'Set QA_SESSION_COOKIE to run authenticated local QA.');

    await context.addCookies([{
        name: process.env.SESSION_COOKIE_NAME ?? 'dscons-session',
        value: sessionCookie,
        domain: 'localhost',
        path: '/',
    }]);
});

test('QA member can open the protected feature routes', async ({ page }) => {
    for (const path of protectedPaths) {
        await test.step(path, async () => {
            const response = await page.goto(path, { waitUntil: 'domcontentloaded' });
            expect(response?.status()).toBeLessThan(400);
            await expect(page).not.toHaveURL(/\/login$/);
        });
    }
});

test('QA member sees seeded content and can use the rich ComposePost editor', async ({ page }, testInfo) => {
    const consoleErrors = [];
    page.on('console', (message) => {
        if (message.type() === 'error') {
            consoleErrors.push(message.text());
        }
    });

    await page.goto('/feed', { waitUntil: 'domcontentloaded' });
    await expect(page.getByText('[TEST] Bài viết thường')).toBeVisible();

    await page.getByRole('button', { name: 'Mở trình tạo bài viết' }).click();
    await expect(page.getByRole('heading', { name: 'Tạo bài viết' })).toBeVisible();

    const editor = page.getByLabel('Nội dung bài viết');
    const richEditor = editor.locator('.ProseMirror');
    await expect(richEditor).toBeEditable();
    await richEditor.fill('[TEST] Rich editor');
    await expect(richEditor).toHaveText('[TEST] Rich editor');
    await expect(page.getByRole('button', { name: 'In đậm' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'In nghiêng' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Danh sách', exact: true })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Trích dẫn' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Chèn liên kết' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Chèn biểu tượng' })).toBeVisible();
    await page.getByRole('button', { name: 'Đóng trình tạo bài viết' }).focus();
    await expect(page.getByRole('button', { name: 'Đóng trình tạo bài viết' })).toBeFocused();
    expect(consoleErrors).toEqual([]);

    await page.screenshot({
        path: `output/playwright/${testInfo.project.name}-authenticated-compose-preview.png`,
        fullPage: true,
    });
});
