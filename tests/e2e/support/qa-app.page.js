import { expect } from '@playwright/test';

export async function attachQaSession(context, envName = 'QA_SESSION_COOKIE') {
    const sessionCookie = process.env[envName];
    if (!sessionCookie) {
        return false;
    }

    await context.addCookies([{
        name: process.env.SESSION_COOKIE_NAME ?? 'dscons-session',
        value: sessionCookie,
        domain: 'localhost',
        path: '/',
    }]);

    return true;
}

export class QaAppPage {
    constructor(page) {
        this.page = page;
    }

    async goto(path) {
        const response = await this.page.goto(path, { waitUntil: 'domcontentloaded' });

        expect(response?.status(), path).toBeLessThan(400);
        await expect(this.page).not.toHaveURL(/\/login$/);

        return response;
    }

    async expectText(text) {
        const matches = this.page.locator('#main-area').getByText(text, { exact: false });
        await this.expectVisibleMatch(matches, text);
    }

    async expectGlobalText(text) {
        const matches = this.page.getByText(text, { exact: false });
        await this.expectVisibleMatch(matches, text);
    }

    async expectVisibleMatch(matches, text) {
        const count = await matches.count();

        for (let index = 0; index < count; index += 1) {
            if (await matches.nth(index).isVisible()) {
                return;
            }
        }

        throw new Error(`Visible text not found: ${text}`);
    }

    async openNotifications() {
        const bells = this.page.locator('.notification-bell button[aria-label="Thông báo"]');
        let bell;

        for (let index = 0; index < await bells.count(); index += 1) {
            if (await bells.nth(index).isVisible()) {
                bell = bells.nth(index);
                break;
            }
        }

        expect(bell, 'visible notification bell').toBeTruthy();
        await bell.click();
        await expect(this.page.locator('.notification-dropdown').first()).toBeVisible();
    }
}
