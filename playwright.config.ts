import { defineConfig, devices } from '@playwright/test'

/**
 * E2E configuration.
 *
 * The Testbench "workbench" application is booted with `composer run serve`
 * (which proxies to `testbench serve`) and exercised against a real Livewire
 * runtime so optimistic-update / rollback behaviour is covered end to end.
 */
const PORT = Number(process.env.E2E_PORT ?? 8000)
const BASE_URL = process.env.E2E_BASE_URL ?? `http://127.0.0.1:${PORT}`

// When the workbench server is started separately (e.g. across Docker
// containers) set `E2E_EXTERNAL=1` so Playwright targets it instead of
// trying to boot its own PHP process.
const useExternalServer = !!process.env.E2E_EXTERNAL

export default defineConfig({
  testDir: 'tests/e2e',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: process.env.CI
    ? [['github'], ['html', { open: 'never' }]]
    : [['list'], ['html', { open: 'never' }]],
  use: {
    baseURL: BASE_URL,
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
    {
      name: 'mobile',
      use: { ...devices['Pixel 5'] },
    },
  ],
  ...(useExternalServer
    ? {}
    : {
        webServer: {
          command: `composer run serve -- --port=${PORT}`,
          url: BASE_URL,
          reuseExistingServer: !process.env.CI,
          timeout: 120_000,
        },
      }),
})
