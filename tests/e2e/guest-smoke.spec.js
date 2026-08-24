import { expect, test } from '@playwright/test';

test.describe('guest smoke flows', () => {
    test('login exposes the configured authentication mode', async ({ page }, testInfo) => {
        const authMode = process.env.QA_AUTH_MODE ?? 'password';
        await page.goto('/login');

        await expect(page.getByRole('heading', { name: 'Đăng nhập' })).toBeVisible();
        if (authMode === 'google') {
            await expect(page.getByRole('link', { name: /Đăng nhập bằng Google/i })).toHaveAttribute(
                'href',
                /auth\/google\/redirect/
            );
            await expect(page.getByLabel('Email')).toHaveCount(0);
            await expect(page.getByLabel('Mật khẩu')).toHaveCount(0);
        } else {
            await expect(page.getByLabel('Email')).toBeVisible();
            await expect(page.getByRole('textbox', { name: 'Mật khẩu' })).toBeVisible();
            await expect(page.getByRole('link', { name: /Đăng nhập bằng Google/i })).toHaveCount(0);
        }

        await page.screenshot({
            path: `output/playwright/${testInfo.project.name}-login.png`,
            fullPage: true,
        });
    });

    test('legacy registration and password URLs follow the configured auth mode', async ({ page }) => {
        const authMode = process.env.QA_AUTH_MODE ?? 'password';
        await page.goto('/register');
        if (authMode === 'google') {
            await expect(page).toHaveURL(/\/login$/);
            await expect(page.getByRole('link', { name: /đăng nhập bằng Google/i })).toBeVisible();
        } else {
            await expect(page.getByRole('heading', { name: 'Tham gia cộng đồng' })).toBeVisible();
        }

        await page.goto('/quen-mat-khau');
        if (authMode === 'google') {
            await expect(page).toHaveURL(/\/login$/);
            await expect(page.getByText(/chỉ hỗ trợ đăng nhập bằng Google/i)).toBeVisible();
        } else {
            await expect(page.getByRole('heading', { name: 'Quên mật khẩu\?' })).toBeVisible();
        }

        await page.goto('/dat-lai-mat-khau/test-token');
        if (authMode === 'google') {
            await expect(page).toHaveURL(/\/login$/);
            await expect(page.getByText(/chỉ hỗ trợ đăng nhập bằng Google/i)).toBeVisible();
        } else {
            await expect(page.getByRole('heading', { name: 'Đặt lại mật khẩu' })).toBeVisible();
        }
    });

    test('guest is redirected away from a protected feed', async ({ page }) => {
        await page.goto('/feed');
        await expect(page).toHaveURL(/\/login$/);
    });
});
