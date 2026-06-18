import { expect, test } from '@playwright/test'

function isoToday(): string {
  const now = new Date()
  const pad = (n: number) => String(n).padStart(2, '0')
  return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`
}

test.beforeEach(async ({ page }) => {
  await page.goto('/')
})

test('syncs a typed date to the server via live binding', async ({ page }) => {
  await page.fill('#birthday-input', '2026-06-18')
  await expect(page.getByTestId('birthday-value')).toHaveText('2026-06-18')
})

test('selects a date from the calendar grid', async ({ page }) => {
  await page.click('#birthday-input')
  const cell = page
    .locator('#birthday-panel [role="gridcell"]:not([data-outside-month]):not([data-disabled])')
    .filter({ hasText: /^15$/ })
    .first()
  const iso = await cell.getAttribute('data-date')
  await cell.click()
  await expect(page.getByTestId('birthday-value')).toHaveText(iso ?? '')
})

test('clears the value', async ({ page }) => {
  await page.fill('#birthday-input', '2026-06-18')
  await expect(page.getByTestId('birthday-value')).toHaveText('2026-06-18')
  await page.click('[data-testid="birthday-section"] button[aria-label="Clear"]')
  await expect(page.getByTestId('birthday-value')).toHaveText('')
})

test('the today button selects today', async ({ page }) => {
  await page.click('#birthday-input')
  await page.click('#birthday-panel >> text=Today')
  await expect(page.getByTestId('birthday-value')).toHaveText(isoToday())
})

test('supports keyboard-only selection', async ({ page }) => {
  await page.focus('#birthday-input')
  await page.keyboard.press('ArrowDown') // open + focus grid
  // The popover opens and moves focus into the grid asynchronously (Alpine
  // transition + $nextTick). Wait for the active day to actually own focus
  // before sending further keys, otherwise they race the open and get dropped.
  await expect(page.locator('#birthday-panel')).toBeVisible()
  await expect(page.locator('#birthday-panel [role="gridcell"][tabindex="0"]')).toBeFocused()
  await page.keyboard.press('ArrowRight')
  await page.keyboard.press('Enter')
  await expect(page.getByTestId('birthday-value')).toHaveText(/^\d{4}-\d{2}-\d{2}$/)
})

test('selects a time', async ({ page }) => {
  await page.click('#opens-input')
  await page.click('#opens-panel [role="option"] >> text=09:30')
  await expect(page.getByTestId('opens-value')).toHaveText('09:30:00')
})

test('types a datetime with separate display and value formats', async ({ page }) => {
  await page.fill('#starts-input', '2026-06-18 09:30')
  await expect(page.getByTestId('starts-value')).toHaveText('2026-06-18T09:30:00')
})

test('rolls back when the server overrides the value', async ({ page }) => {
  await page.fill('#reject-input', '2026-06-18')
  // The server forces the value to 2000-01-01; the optimistic UI must reconcile.
  await expect(page.getByTestId('reject-value')).toHaveText('2000-01-01')
  await expect(page.locator('#reject-input')).toHaveValue('2000-01-01')
})

test('shows a validation error and recovers', async ({ page }) => {
  await page.click('[data-testid="validate-form"] button[type="submit"]')
  await expect(page.getByTestId('published-error')).toBeVisible()

  await page.fill('#published-input', '2026-06-18')
  // Wait for the live binding to reach the server before submitting.
  await expect(page.getByTestId('published-value')).toHaveText('2026-06-18')
  await page.click('[data-testid="validate-form"] button[type="submit"]')
  await expect(page.getByTestId('saved')).toBeVisible()
})

test('renders in dark mode', async ({ page }) => {
  await page.goto('/?dark=1')
  await expect(page.locator('html.dark')).toHaveCount(1)
  await page.click('#birthday-input')
  await expect(page.locator('#birthday-panel')).toBeVisible()
})

test('opens and closes on a mobile viewport', async ({ page }) => {
  await page.click('#birthday-input')
  await expect(page.locator('#birthday-panel')).toBeVisible()
  await page.keyboard.press('Escape')
  await expect(page.locator('#birthday-panel')).toBeHidden()
})

test('keeps the popover within the viewport on a short screen', async ({ page }) => {
  // A short viewport with the tall datetime panel would overflow off-screen
  // without the height clamp in positionPanel(). getBoundingClientRect is
  // viewport-relative, so we can assert the panel stays fully on-screen.
  await page.setViewportSize({ width: 420, height: 480 })
  await page.click('#starts-input')
  const panel = page.locator('#starts-panel')
  await expect(panel).toBeVisible()
  await expect(panel).toHaveCSS('opacity', '1')

  const rect = await panel.evaluate((el) => {
    const r = el.getBoundingClientRect()
    return {
      top: r.top,
      bottom: r.bottom,
      left: r.left,
      right: r.right,
      vw: window.innerWidth,
      vh: window.innerHeight,
    }
  })

  expect(rect.top).toBeGreaterThanOrEqual(-1)
  expect(rect.bottom).toBeLessThanOrEqual(rect.vh + 1)
  expect(rect.left).toBeGreaterThanOrEqual(-1)
  expect(rect.right).toBeLessThanOrEqual(rect.vw + 1)
})
