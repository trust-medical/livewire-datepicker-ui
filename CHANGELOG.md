# Changelog

All notable changes to `trust-medical/livewire-datepicker-ui` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.2.0] - 2026-09-24

### Added

- Hovering or keyboard-focusing a day now highlights its whole week row
  (`week` slot), not just the individual day cell.

### Changed

- Compacted the date grid: removed the vertical margin between week rows and
  shortened the weekday header row, while keeping the 44×44px (WCAG 2.5.5)
  touch target on day cells unchanged.

## [0.1.0] - 2026-06-18

### Added

- Initial release of the date / time / datetime picker UI package.
- Blade components: `<x-datepicker>`, `<x-date-picker>`, `<x-time-picker>`, `<x-date-time-picker>`.
- Alpine.js powered UI with optimistic Livewire binding via `$wire.entangle` and
  automatic rollback on server rejection / validation failure.
- Dependency-free, timezone-safe date handling (civil-date model) with separate
  display / submit formats using PHP date tokens.
- `min` / `max`, disabled dates, disabled weekdays, disabled times and minute-step
  constraints, mirrored between PHP and TypeScript.
- Full WAI-ARIA keyboard navigation (combobox + dialog + grid).
- Tailwind-first theming: config class map, per-instance `classes` prop, named
  themes, data-attribute state styling and dark mode support.
- Laravel 11 / 12 / 13 and Livewire 3 / 4 support.

[Unreleased]: https://github.com/trust-medical/livewire-datepicker-ui/compare/v0.2.0...HEAD
[0.2.0]: https://github.com/trust-medical/livewire-datepicker-ui/compare/v0.1.0...v0.2.0
[0.1.0]: https://github.com/trust-medical/livewire-datepicker-ui/releases/tag/v0.1.0
