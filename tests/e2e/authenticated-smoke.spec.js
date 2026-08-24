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

test('QA member sees seeded content and can open ComposePost preview', async ({ page }, testInfo) => {
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

    await page.getByLabel('Nội dung bài viết').fill('**[TEST] Preview**');
    await expect(page.getByRole('button', { name: 'In đậm' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'In nghiêng' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Danh sách', exact: true })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Trích dẫn' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Chèn liên kết' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Chèn video YouTube' })).toBeVisible();
    await page.getByRole('tab', { name: 'Xem trước' }).click();
    await expect(page.getByLabel('Xem trước bài viết')).toContainText('[TEST] Preview');
    await expect(page.getByLabel('Xem trước bài viết').locator('strong')).toHaveText('[TEST] Preview');

    await page.getByRole('tab', { name: 'Soạn thảo' }).click();
    await expect(page.getByLabel('Nội dung bài viết')).toHaveValue('**[TEST] Preview**');
    await page.getByRole('tab', { name: 'Xem trước' }).click();
    await expect(page.getByLabel('Xem trước bài viết')).toContainText('[TEST] Preview');
    await page.getByRole('button', { name: 'Đóng trình tạo bài viết' }).focus();
    await expect(page.getByRole('button', { name: 'Đóng trình tạo bài viết' })).toBeFocused();
    expect(consoleErrors).toEqual([]);

    await page.screenshot({
        path: `output/playwright/${testInfo.project.name}-authenticated-compose-preview.png`,
        fullPage: true,
    });
});
