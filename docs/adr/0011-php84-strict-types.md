# ADR-0011: PHP 8.4+, strict types throughout, modern OOP

- Status: Accepted
- Date: 2026-10-10

## Context

The core is self-written to learn PHP OOP and still stay serious (0008). Without a language baseline, code slides into loose arrays and PHP 5 style.

## Decision

- **PHP ≥ 8.4** (8.5 is fine).
- Every PHP file: `declare(strict_types=1);` immediately after `<?php`.
- PSR-4 namespaces (`Framework\`, `App\`); no files without a namespace except `public/index.php`.
- Visible types: properties, parameters, returns. Constructor property promotion and `readonly` where the value does not change.
- `enum` for closed sets (HTTP method), `match` instead of a long `switch` where it is clearer.
- No `@` error suppression, no untyped `mixed` for convenience.
- Property hooks and other 8.4 extras: use when they add clarity, not to use them.

## Consequences

Each phase in the [roadmap](../roadmap.md) has a PHP learning focus. Reviews check types and namespaces, not only “it runs”.
