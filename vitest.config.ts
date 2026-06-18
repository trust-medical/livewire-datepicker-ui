import { resolve } from 'node:path'
import { fileURLToPath } from 'node:url'
import { defineConfig } from 'vitest/config'

const rootDir = fileURLToPath(new URL('.', import.meta.url))

export default defineConfig({
  resolve: {
    alias: {
      '@datepicker': resolve(rootDir, 'resources/js/datepicker'),
    },
  },
  test: {
    environment: 'happy-dom',
    globals: true,
    include: ['tests/js/**/*.{test,spec}.ts'],
    setupFiles: ['tests/js/setup.ts'],
    coverage: {
      provider: 'v8',
      reportsDirectory: 'coverage/js',
      include: ['resources/js/datepicker/**/*.ts'],
      exclude: ['resources/js/datepicker/**/*.d.ts', 'resources/js/datepicker/index.ts'],
    },
  },
})
