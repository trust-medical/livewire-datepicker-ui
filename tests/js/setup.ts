import { afterEach } from 'vitest'

// Keep the DOM clean between tests so multiple-instance and lifecycle
// assertions never leak state across files.
afterEach(() => {
  document.body.innerHTML = ''
  document.head.querySelectorAll('[data-datepicker-style]').forEach((node) => node.remove())
})
