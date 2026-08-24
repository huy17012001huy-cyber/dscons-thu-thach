import { defineConfig, devices } from '@playwright/test';

const baseURL = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8080';
const executablePath = process.env.PLAYWRIGHT_EXECUTABLE_PATH;

export default defineConfig({
    testDir: './tests/e2e',
    outputDir: 'output/playwright/test-results',
    timeout: 30_000,
    expect: { timeout: 5_000 },
    fullyParallel: true,
    reporter: [
        ['list'],
        ['html', { outputFolder: 'output/playwright/report', open: 'never' }],
    ],
    use: {
        baseURL,
        ignoreHTTPSErrors: true,
        launchOptions: executablePath ? { executablePath } : undefined,
        trace: 'on-first-retry',
        screenshot: 'only-on-failure',
        video: 'off',
    },
    projects: [
        { name: 'desktop-chromium', use: { ...devices['Desktop Chrome'] } },
        {
            name: 'mobile-chromium',
            use: { ...devices['Desktop Chrome'], viewport: { width: 390, height: 844 }, isMobile: false },
        },
    ],
});
