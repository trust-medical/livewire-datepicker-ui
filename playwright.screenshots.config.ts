import { defineConfig } from '@playwright/test'

import base from './playwright.config'

/**
 * Screenshot capture config. Reuses the E2E settings (baseURL / external server
 * detection) but points `testDir` at tests/screenshots so the README capture
 * spec runs in isolation from the behavioural E2E suite.
 */
export default defineConfig({
  ...base,
  testDir: 'tests/screenshots',
})
