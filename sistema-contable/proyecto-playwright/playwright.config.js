// @ts-check
const { defineConfig } = require('@playwright/test');

const baseURL = process.env.BASE_URL || 'http://127.0.0.1:8000';

module.exports = defineConfig({
  testDir: './tests',
  fullyParallel: false,
  workers: 1,
  timeout: 60 * 1000,
  retries: 0,
  use: {
    baseURL,
    headless: true,
  },
  webServer: {
    command: 'php ..\\artisan serve --host=127.0.0.1 --port=8000',
    url: baseURL,
    reuseExistingServer: true,
    timeout: 120 * 1000,
  },
});
