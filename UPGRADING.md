# Upgrade Guide

This package follows [Semantic Versioning](https://semver.org/). The **public
surface** that is covered by SemVer is intentionally small:

- The Blade component tags and their documented props.
- The published `config/datepicker.php` keys.
- The published view names and partial structure.
- The JavaScript public exports from the package entry point
  (`Datepicker`, `registerDatepicker`, exported types).
- The documented data-attributes used for state styling.

Anything not listed above (internal classes under `Domain`, `Application`,
`Infrastructure`, internal TS modules, DOM structure details) may change in a
minor release.

## Version compatibility

| Package | PHP        | Laravel      | Livewire   | Alpine |
| ------- | ---------- | ------------ | ---------- | ------ |
| 0.x     | 8.2 – 8.4  | 11 / 12 / 13 | 3 / 4      | 3      |

> PHP 8.2 cannot be combined with Laravel 13 (Testbench 11 / Laravel 13 require
> PHP 8.3+). All other combinations in the table are supported and tested.

## Upgrading within 0.x

Until a 1.0 release, minor `0.x` bumps may contain breaking changes (per SemVer
for `0.y.z`). Each such change is called out in `CHANGELOG.md` with a migration
note. After publishing assets, always re-run:

```bash
php artisan vendor:publish --tag=datepicker-assets --force
```

to keep the prebuilt JS/CSS in `public/vendor/datepicker` in sync with the
installed package version.

## When 1.0 lands

From 1.0 onward, breaking changes are reserved for major versions. Deprecations
will be announced at least one minor version before removal.
