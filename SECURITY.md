# Security Policy

## Supported versions

Security fixes are provided for the latest minor release line. Because this is a
UI package with no server-side persistence or authentication logic, the attack
surface is limited to rendering (output escaping) and the client runtime.

| Version | Supported |
| ------- | --------- |
| 0.x     | ✅        |

## Reporting a vulnerability

Please **do not** open a public issue for security problems.

Email **fukuhara@trust-medical.jp** with:

- A description of the issue and its impact.
- Steps to reproduce (a minimal Blade/Livewire snippet is ideal).
- Affected versions.

You will receive an acknowledgement within 5 business days. Once a fix is ready
we will coordinate a disclosure timeline and credit you in the changelog unless
you prefer to remain anonymous.

## Scope notes

- All user-facing values are rendered through Blade's escaping and never via
  `{!! !!}`. Class maps and labels are treated as configuration, not user input.
- The package performs **no** timezone conversion and **no** business logic; see
  the "Timezone & date-only behaviour" section of the README for the documented
  contract.
