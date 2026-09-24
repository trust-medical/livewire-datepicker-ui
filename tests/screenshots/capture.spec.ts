import { expect, test } from '@playwright/test'

/**
 * Captures the README screenshots of the default-styled picker.
 *
 * Run via `make screenshots` (requires `make serve` running and the workbench
 * Tailwind sheet built with `pnpm workbench:css`). It is intentionally outside
 * `testDir` (tests/e2e) so the normal E2E suite never runs it.
 *
 * Each shot opens the picker on the `/showcase` page and writes a tightly
 * cropped PNG — the union of the input and the open panel, padded — into
 * `docs/images/`.
 */
const INPUT = '#showcase-input'
const PANEL = '#showcase-panel'

type Shot = { name: string; url: string; hover?: boolean }

const shots: Shot[] = [
  { name: 'date-light', url: '/showcase?mode=date' },
  { name: 'date-dark', url: '/showcase?mode=date&dark=1' },
  { name: 'time-light', url: '/showcase?mode=time' },
  { name: 'datetime-light', url: '/showcase?mode=datetime' },
  // Japanese-localized UI (?lang=ja): weekday/month names + footer buttons.
  { name: 'date-ja-light', url: '/showcase?mode=date&lang=ja' },
  { name: 'datetime-ja-light', url: '/showcase?mode=datetime&lang=ja' },
  // Demonstrates the whole-week-row hover highlight (see `week` in
  // config/datepicker.php). Hovers a day a few rows in so the shot always
  // lands on an in-month week regardless of which weekday the 1st falls on.
  { name: 'date-hover-light', url: '/showcase?mode=date', hover: true },
]

for (const shot of shots) {
  test(`capture ${shot.name}`, async ({ page }) => {
    await page.setViewportSize({ width: 820, height: 1000 })
    await page.goto(shot.url)

    await page.click(INPUT)
    await expect(page.locator(PANEL)).toBeVisible()
    // Guard against an unstyled capture: the input now ships unstyled (browser
    // default), so check the popover instead — the compiled Tailwind sheet gives
    // it a 1px border. Without the sheet this assertion fails fast.
    await expect(page.locator(PANEL)).toHaveCSS('border-top-width', '1px')
    // Wait for the open (opacity) transition to finish before measuring.
    await expect(page.locator(PANEL)).toHaveCSS('opacity', '1')

    // Reveal the selected time so the highlighted option shows in the shot.
    const selectedTime = page.locator(`${PANEL} [role="listbox"] [data-selected]`)
    if ((await selectedTime.count()) > 0) {
      await selectedTime.first().scrollIntoViewIfNeeded()
    }

    const input = await page.locator(INPUT).boundingBox()
    const panel = await page.locator(PANEL).boundingBox()
    if (input === null || panel === null) {
      throw new Error('Could not measure the picker layout')
    }

    const pad = 28
    const x = Math.max(0, Math.min(input.x, panel.x) - pad)
    const y = Math.max(0, Math.min(input.y, panel.y) - pad)
    const right = Math.max(input.x + input.width, panel.x + panel.width) + pad
    const bottom = Math.max(input.y + input.height, panel.y + panel.height) + pad

    if (shot.hover) {
      // Third grid row, a middle column: reliably an in-month day regardless
      // of which weekday the 1st of the current month falls on.
      await page.locator(`${PANEL} [role="row"]`).nth(2).locator('button[data-date]').nth(3).hover()
    }

    await page.screenshot({
      path: `docs/images/datepicker-${shot.name}.png`,
      clip: { x, y, width: right - x, height: bottom - y },
    })
  })
}
