import { expect, test } from '@playwright/test';
import { attachQaSession, QaAppPage } from './support/qa-app.page.js';

test.describe('QA member feature regression', () => {
    test.beforeEach(async ({ context }) => {
        const attached = await attachQaSession(context);
        test.skip(!attached, 'Set QA_SESSION_COOKIE to run authenticated local QA.');
    });

    test('renders seeded content across member feature routes', async ({ page }) => {
        const app = new QaAppPage(page);
        const routeChecks = [
            ['/feed?tab=cot', '[TEST] Bài CỐT'],
            ['/feed?tab=signal', '[TEST] Tín hiệu ngắn'],
            ['/hoi-dap', '[TEST] Câu hỏi kỹ thuật'],
            ['/khoa-hoc', '[TEST] Khóa học Revit cơ bản'],
            ['/challenge', '[TEST] Challenge Revit 21 ngày'],
            ['/su-kien', '[TEST] Sự kiện Revit live'],
            ['/marketplace', '[TEST] Tài nguyên Revit miễn phí'],
            ['/leaderboard', '[TEST] QA Admin'],
        ];

        for (const [path, text] of routeChecks) {
            await test.step(path, async () => {
                await app.goto(path);
                await app.expectText(text);
                if (path === '/su-kien') {
                    await app.expectText('Đã đăng ký');
                }
                if (path === '/marketplace') {
                    await app.expectText('Đã mua');
                }
            });
        }
    });

    test('covers profile, search and notification states', async ({ page }, testInfo) => {
        const app = new QaAppPage(page);

        await app.goto('/@qa-member');
        await app.expectText('[TEST] QA Member');
        await expect(page.locator('.profile-page:visible').getByRole('button', { name: 'Chỉnh sửa hồ sơ' })).toBeVisible();

        await app.goto('/search?q=QA');
        await app.expectText('[TEST] QA Member');
        await app.expectText('[TEST] QA');

        await app.goto('/search?q=x');
        await app.expectText('Nhập ít nhất 2 ký tự');

        await app.goto('/feed');
        await app.openNotifications();
        await app.expectGlobalText('[TEST] Bạn có một thông báo để kiểm tra');
        await expect(page.locator('.notification-dropdown:visible')).toBeVisible();

        await page.screenshot({
            path: 'output/playwright/' + testInfo.project.name + '-feature-regression.png',
            fullPage: true,
        });
    });

    test('opens the account menu and member account pages', async ({ page }, testInfo) => {
        const app = new QaAppPage(page);

        await app.goto('/feed');
        const trigger = testInfo.project.name === 'mobile-chromium'
            ? page.locator('#mobile-account-trigger')
            : page.locator('#user-panel:visible').last();
        await trigger.click();
        const menu = page.locator('.account-menu:visible').last();
        await expect(menu.getByText('Hồ sơ của bạn')).toBeVisible();
        await expect(menu.getByText('Cài đặt tài khoản')).toBeVisible();
        await expect(menu.getByText('Đăng xuất')).toBeVisible();

        await page.goto('/tai-khoan/cai-dat');
        await expect(page.getByRole('heading', { name: 'Lịch sử đã mua' })).toBeVisible();
        await expect(page.getByRole('heading', { name: 'Thông tin xuất hóa đơn' })).toBeVisible();

        await page.goto('/ho-so/chinh-sua');
        await expect(page.getByRole('heading', { name: 'Sửa hồ sơ' })).toBeVisible();
        await expect(page.getByLabel('Email')).toHaveValue(/@/);
    });
});

test.describe('QA admin smoke', () => {
    test.beforeEach(async ({ context }) => {
        const attached = await attachQaSession(context, 'QA_ADMIN_SESSION_COOKIE');
        test.skip(!attached, 'Set QA_ADMIN_SESSION_COOKIE to run admin local QA.');
    });

    test('admin can open dashboard and member provisioning modal', async ({ page }) => {
        const app = new QaAppPage(page);

        await app.goto('/admin');
        await app.expectText('Admin Dashboard');

        await app.goto('/admin/users');
        await app.expectText('Quản lý người dùng');
        await page.getByRole('button', { name: /Tạo thành viên/ }).click();
        await expect(page.locator('[role="dialog"]:visible').getByRole('heading', { name: 'Tạo thành viên mới' })).toBeVisible();
        await app.expectText('Không tạo hoặc gửi mật khẩu');
        await expect(page.locator('[role="dialog"]:visible').locator('input[type="email"]')).toBeVisible();
    });
});
