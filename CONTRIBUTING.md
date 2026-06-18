# Contributing

Thanks for considering a contribution to `trust-medical/livewire-datepicker-ui`.

## Development environment

Everything runs inside Docker, so you only need **Docker** and **Docker Compose**
on the host. No local PHP, Node, Composer or pnpm installation is required.

```bash
# Build the php + node images
make build-images

# Install Composer + pnpm dependencies
make install
```

> Prefer VS Code? Open the folder in the provided **Dev Container**
> (`.devcontainer/devcontainer.json`) — it ships PHP 8.3, Composer, Node, pnpm,
> Playwright and the recommended editor extensions in one container.

## Common tasks

| Command          | What it does                                              |
| ---------------- | -------------------------------------------------------- |
| `make test`      | PHP (Pest) + JS (Vitest) suites                          |
| `make test-php`  | PHP suite only                                           |
| `make test-js`   | JS suite only                                            |
| `make serve`     | Start the Testbench workbench app at `localhost:8000`    |
| `make e2e`       | Playwright E2E (run `make serve` in another shell first) |
| `make screenshots` | Regenerate the README images (run `make serve` first)  |
| `make build`     | Build the JS library into `dist/`                        |
| `make lint`      | Pint (PHP) + ESLint + Prettier checks                    |
| `make typecheck` | `tsc --noEmit` + Larastan (max level)                    |
| `make format`    | Auto-fix code style (Pint + Prettier)                    |
| `make ci`        | The full local pipeline (lint, typecheck, test, build)   |

## Expectations

- **No new runtime dependencies.** The PHP package depends only on Laravel
  contracts; the JS depends only on Alpine (a peer dependency).
- **Tests are required.** New behaviour needs unit coverage on both sides where
  applicable; date format/parse changes must update
  `tests/fixtures/format-cases.json` so the PHP and TS implementations stay in
  sync.
- **Static analysis must pass.** `make lint` and `make typecheck` are clean
  (Larastan max level, TypeScript `strict`, no implicit `any`).
- **Accessibility is a feature.** Keep the WAI-ARIA roles, keyboard interactions
  and focus management intact; add E2E coverage for new interactions.
- **Keep the public surface small.** Avoid adding new public exports/props
  unless necessary; prefer composition over new API.

## CI matrix & dependency policy

`tests-php` runs the suite across Laravel 11 / 12 / 13 (PHP 8.2–8.4) by
`composer require`-ing the matching Testbench + Livewire versions. Two notes for
maintainers:

- **Pest 3 _and_ 4.** `pestphp/pest` is constrained to `^3.5 || ^4.0` because
  Pest 4 (via `pest-plugin-laravel ^4`) is the only line that supports Laravel
  13, while Pest 4 requires PHP 8.3+. Composer therefore resolves Pest 4 on
  PHP 8.3+ and falls back to Pest 3 on the PHP 8.2 / Laravel 11 row.
- **`config.policy.advisories.block: false`.** Composer 2.10 blocks installing
  package versions with open security advisories during `update`. Older but
  still-supported Laravel 11 releases carry framework advisories we cannot fix
  from a library, which would otherwise break the Laravel 11 CI rows. Disabling
  the _block_ (advisories are still **reported**, not hidden) lets the matrix
  install Laravel 11 for testing. It lives under `config`, so it only applies
  when this repo is the Composer root — consumers never inherit it.

## Regenerating the README screenshots

The images under `docs/images/` are generated from the workbench `/showcase`
page with real, compiled Tailwind v4 styling — never edited by hand. To refresh
them after a styling change:

```bash
make serve          # in one shell (starts the workbench server)
make screenshots    # in another shell — builds dist + Tailwind, captures PNGs
```

`make screenshots` runs `pnpm build` (the IIFE bundle), `pnpm workbench:css`
(compiles `workbench/resources/css/app.css` → `workbench/build/app.css`, scanning
the package class map and views) and the Playwright capture spec
(`tests/screenshots/capture.spec.ts`), writing `docs/images/datepicker-*.png`.
Commit the updated PNGs alongside the styling change.

## Pull requests

1. Create a feature branch off `main`.
2. Add a `## [Unreleased]` entry to `CHANGELOG.md`.
3. Make sure `make ci` and `make e2e` are green.
4. Open the PR with a clear description and rationale.
