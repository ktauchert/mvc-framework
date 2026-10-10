# ADR-0008: Serious framework, self-built core

- Status: Accepted
- Date: 2026-10-10

## Context

“Learning project” meant: you build the core yourself, no dumped skeleton. The product goal is still a **usable, secure framework** for a PHP API plus SPA flavors (React/TanStack first, Vue later) — intuitive for developers, safe and smooth for end users.

## Alternatives

1. Tutorial toy — show patterns, security and DX secondary.
2. Clone Laravel in one shot — you do not own the core.
3. **Serious design, stepwise implementation** — ADRs and security from the start; create-project later as packaging of the built core.

## Decision

Option 3. Quality bar: prepared statements, deliberate auth/session, no secrets in the repo, predictable CLI (0005/0006), an SPA that feels fast (no extra round trip without reason).

“Flavor” = swappable view (0004/0006) plus later swappable persistence adapters (0007), not five half cores.

## Consequences

- Default engine is Postgres (0007), not SQLite. Skipping features is fine; insecure shortcuts “because it is a learning build” are not.
