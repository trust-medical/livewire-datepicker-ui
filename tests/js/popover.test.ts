import { describe, expect, it } from 'vitest'
import { positionPanel } from '@datepicker/infrastructure/popover'

/**
 * happy-dom has no layout engine, so `offsetHeight`/`offsetWidth`,
 * `getBoundingClientRect()` and `window.innerHeight`/`innerWidth` all read 0.
 * We stub them to drive the positioning math deterministically.
 */
function setup(opts: {
  innerHeight: number
  innerWidth?: number
  anchorTop: number
  anchorBottom: number
  anchorLeft?: number
  anchorRight?: number
  panelHeight: number
  panelWidth?: number
}): { panel: HTMLElement; anchor: HTMLElement } {
  Object.defineProperty(window, 'innerHeight', { configurable: true, value: opts.innerHeight })
  Object.defineProperty(window, 'innerWidth', {
    configurable: true,
    value: opts.innerWidth ?? 1024,
  })

  const panel = document.createElement('div')
  Object.defineProperty(panel, 'offsetHeight', { configurable: true, value: opts.panelHeight })
  Object.defineProperty(panel, 'offsetWidth', { configurable: true, value: opts.panelWidth ?? 200 })

  const left = opts.anchorLeft ?? 0
  const right = opts.anchorRight ?? 200
  const anchor = document.createElement('input')
  anchor.getBoundingClientRect = () =>
    ({
      top: opts.anchorTop,
      bottom: opts.anchorBottom,
      left,
      right,
      width: right - left,
      height: opts.anchorBottom - opts.anchorTop,
      x: left,
      y: opts.anchorTop,
      toJSON: () => ({}),
    }) as DOMRect

  return { panel, anchor }
}

const MARGIN = 8

describe('positionPanel', () => {
  it('fixes the panel to the viewport (so it never grows the document)', () => {
    const { panel, anchor } = setup({
      innerHeight: 1000,
      anchorTop: 100,
      anchorBottom: 140,
      panelHeight: 300,
    })

    positionPanel(panel, anchor, 'bottom-start')

    expect(panel.style.position).toBe('fixed')
  })

  it('opens downward below the anchor and caps height to the space below', () => {
    const { panel, anchor } = setup({
      innerHeight: 1000,
      innerWidth: 800,
      anchorTop: 100,
      anchorBottom: 140,
      anchorLeft: 50,
      anchorRight: 250,
      panelHeight: 300,
      panelWidth: 200,
    })

    positionPanel(panel, anchor, 'bottom-start')

    expect(panel.style.top).toBe('148px') // anchorBottom (140) + gap (8)
    expect(panel.style.bottom).toBe('auto')
    expect(panel.style.left).toBe('50px') // anchorLeft
    expect(panel.style.right).toBe('auto')
    expect(panel.style.overflowY).toBe('auto')
    // spaceBelow (860) - gap (8) - margin (8)
    expect(panel.style.maxHeight).toBe('844px')
  })

  it('flips a bottom placement upward when there is no room below but room above', () => {
    const { panel, anchor } = setup({
      innerHeight: 1000,
      anchorTop: 800,
      anchorBottom: 840,
      panelHeight: 300,
    })

    positionPanel(panel, anchor, 'bottom-start')

    // anchorTop (800) - gap (8) - usedHeight (300)
    expect(panel.style.top).toBe('492px')
    expect(panel.style.bottom).toBe('auto')
    // spaceAbove (800) - gap (8) - margin (8)
    expect(panel.style.maxHeight).toBe('784px')
  })

  it('flips a top placement downward when there is no room above but room below', () => {
    const { panel, anchor } = setup({
      innerHeight: 1000,
      anchorTop: 120,
      anchorBottom: 160,
      panelHeight: 400,
    })

    positionPanel(panel, anchor, 'top-start')

    expect(panel.style.top).toBe('168px') // anchorBottom (160) + gap (8) → opened below
  })

  it('never lets an upward panel leave the top of the viewport', () => {
    const { panel, anchor } = setup({
      innerHeight: 600,
      anchorTop: 300,
      anchorBottom: 340,
      panelHeight: 500, // taller than either side
    })

    // top placement; the larger side is above (300) vs below (260) → stays up.
    positionPanel(panel, anchor, 'top-start')

    const top = Number.parseInt(panel.style.top, 10)
    const maxHeight = Number.parseInt(panel.style.maxHeight, 10)
    // spaceAbove (300) - gap (8) - margin (8)
    expect(maxHeight).toBe(284)
    // The clamped panel sits at the top margin and stays fully on screen.
    expect(top).toBeGreaterThanOrEqual(MARGIN)
    expect(top + maxHeight).toBeLessThanOrEqual(600 - MARGIN)
  })

  it('aligns the end edge to the anchor for *-end placements', () => {
    const { panel, anchor } = setup({
      innerHeight: 1000,
      innerWidth: 1000,
      anchorTop: 100,
      anchorBottom: 140,
      anchorLeft: 400,
      anchorRight: 600,
      panelHeight: 200,
      panelWidth: 200,
    })

    positionPanel(panel, anchor, 'bottom-end')

    expect(panel.style.left).toBe('400px') // anchorRight (600) - panelWidth (200)
    expect(panel.style.right).toBe('auto')
  })

  it('clamps horizontally so the panel never runs off the viewport edge', () => {
    const { panel, anchor } = setup({
      innerHeight: 1000,
      innerWidth: 400,
      anchorTop: 100,
      anchorBottom: 140,
      anchorLeft: 350, // near the right edge
      anchorRight: 390,
      panelHeight: 200,
      panelWidth: 200,
    })

    positionPanel(panel, anchor, 'bottom-start')

    // anchorLeft (350) would overflow; clamp to innerWidth (400) - panelWidth (200) - margin (8)
    expect(panel.style.left).toBe('192px')
  })

  it('re-measures on each call by clearing the previous height cap', () => {
    const { panel, anchor } = setup({
      innerHeight: 1000,
      anchorTop: 100,
      anchorBottom: 140,
      panelHeight: 200,
    })

    panel.style.maxHeight = '50px' // stale cap from a previous open
    positionPanel(panel, anchor, 'bottom-start')

    // Recomputed from spaceBelow (860) - gap (8) - margin (8), not the stale 50px.
    expect(panel.style.maxHeight).toBe('844px')
  })
})
