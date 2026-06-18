import { resolve } from 'node:path'
import { fileURLToPath } from 'node:url'
import { defineConfig } from 'vite'
import dts from 'vite-plugin-dts'

const rootDir = fileURLToPath(new URL('.', import.meta.url))

/**
 * Library build.
 *
 * Produces three consumable artefacts in `dist/`:
 *   - `index.mjs`            ESM, tree-shakable, for bundler consumers (`import { Datepicker } from ...`)
 *   - `index.cjs`            CommonJS fallback
 *   - `datepicker.iife.js`   self-registering global build for the Blade `@datepickerScripts` directive
 *
 * `alpinejs` is treated as an external peer dependency and is never bundled,
 * so it is shared with the Alpine instance Livewire ships.
 */
export default defineConfig({
  resolve: {
    alias: {
      '@datepicker': resolve(rootDir, 'resources/js/datepicker'),
    },
  },
  build: {
    target: 'es2022',
    outDir: 'dist',
    emptyOutDir: true,
    sourcemap: true,
    cssCodeSplit: false,
    lib: {
      entry: resolve(rootDir, 'resources/js/datepicker/index.ts'),
      name: 'LivewireDatepicker',
      formats: ['es', 'cjs', 'iife'],
      fileName: (format) => {
        if (format === 'es') return 'index.mjs'
        if (format === 'cjs') return 'index.cjs'
        return 'datepicker.iife.js'
      },
    },
    rollupOptions: {
      external: ['alpinejs'],
      output: {
        exports: 'named',
        globals: { alpinejs: 'Alpine' },
        assetFileNames: (info) =>
          info.name === 'style.css' ? 'datepicker.css' : (info.name ?? 'asset'),
      },
    },
  },
  plugins: [
    dts({
      tsconfigPath: 'tsconfig.build.json',
      rollupTypes: true,
      include: ['resources/js/datepicker/**/*.ts'],
    }),
  ],
})
