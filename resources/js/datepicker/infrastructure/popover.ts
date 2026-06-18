/**
 * Minimal, dependency-free popover positioning. The panel is `fixed` to the
 * viewport, which is deliberate: a fixed element never grows the document, so
 * opening the panel can't toggle the page scrollbar (the cause of the open
 * "flicker"). We flip it above/below the anchor depending on room, cap its
 * height to the viewport (it scrolls internally when space is tight), and clamp
 * it so it always stays fully on screen. Good enough for modern browsers without
 * a positioning library.
 */
export function positionPanel(panel: HTMLElement, anchor: HTMLElement, placement: string): void {
  panel.style.position = 'fixed'

  // Clear any cap from a previous open so we measure the natural size.
  panel.style.maxHeight = ''

  const anchorRect = anchor.getBoundingClientRect()
  const viewportH = window.innerHeight
  const viewportW = window.innerWidth
  const gap = 8 // space between the anchor and the panel
  const margin = 8 // keep at least this far from the viewport edges

  const panelHeight = panel.offsetHeight
  const panelWidth = panel.offsetWidth
  const spaceBelow = viewportH - anchorRect.bottom
  const spaceAbove = anchorRect.top

  // Prefer the requested side; flip only when it can't fit and the other side
  // has more room. 'auto' / anything not top|bottom opens downward.
  let openUp = placement.startsWith('top')
  const preferred = openUp ? spaceAbove : spaceBelow
  const other = openUp ? spaceBelow : spaceAbove
  if (preferred < panelHeight + gap + margin && other > preferred) {
    openUp = !openUp
  }

  // Cap the height to the chosen side; the panel scrolls internally only when
  // the space is genuinely too small. With room to spare maxHeight >=
  // panelHeight, so no scrollbar appears.
  const available = (openUp ? spaceAbove : spaceBelow) - gap - margin
  const maxHeight = Math.max(0, Math.floor(available))
  panel.style.maxHeight = `${maxHeight}px`
  panel.style.overflowY = 'auto'
  const usedHeight = Math.min(panelHeight, maxHeight)

  // Vertical: place above or below the anchor, then clamp inside the viewport.
  let top = openUp ? anchorRect.top - gap - usedHeight : anchorRect.bottom + gap
  top = Math.max(margin, Math.min(top, viewportH - usedHeight - margin))
  panel.style.top = `${Math.round(top)}px`
  panel.style.bottom = 'auto'

  // Horizontal: align the start (left) or end (right) edge to the anchor, then
  // clamp so the panel never runs off the left/right of the viewport.
  let left = placement.endsWith('end') ? anchorRect.right - panelWidth : anchorRect.left
  left = Math.max(margin, Math.min(left, viewportW - panelWidth - margin))
  panel.style.left = `${Math.round(left)}px`
  panel.style.right = 'auto'
}
