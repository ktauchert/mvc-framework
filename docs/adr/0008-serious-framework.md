# ADR-0008: Serious framework, self-built core

- Status: Accepted
- Date: 2026-10-10

## Context

“Learning project” meant: you build the core yourself, no dumped skeleton. The product goal is still a **usable, secure framework**. The view is PHP (ADR-0012), not a JS SPA. JSON under `src/api/` can exist later. Intuitive for developers, safe and smooth for end users.

## Alternatives

1. Tutorial toy — show patterns, security and DX secondary.
2. Clone Laravel in one shot — you do not own the core.
3. **Serious design, stepwise implementation** — ADRs and security from the start; create-project later as packaging of the built core.

## Decision

Option 3. Quality bar: prepared statements, deliberate auth/session, no secrets in the repo, predictable CLI (`make-route` and later commands), HTML pages that feel fast.

“Flavor” = later extras (CMS, API) on the same PHP kernel, plus persistence adapters (0007) — not a second frontend framework.

## Consequences

- Default engine is Postgres (0007), not SQLite. Skipping features is fine; insecure shortcuts “because it is a learning build” are not.
