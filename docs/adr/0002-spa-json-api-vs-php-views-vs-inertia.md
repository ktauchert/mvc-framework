# ADR-0002: View coupling — SPA + JSON API vs PHP views vs Inertia

- Status: Accepted
- Date: 2026-10-10

## Context

Goal: go deeper on PHP (HTTP, routing, controllers, models) and attach a JS view. Classic PHP MVC renders HTML. Laravel starter kits (2026) couple React/Vue through Inertia (PHP picks the page plus props). An SPA talks to PHP only via JSON; PHP then has no `Views/` HTML as the main path.

## Alternatives

1. **PHP templates** — one router, MVC in one language. Little Vue/React learning value.
2. **Inertia-like** — PHP stays the page router; JS is presentation only. Less REST; a custom protocol to rebuild.
3. **SPA + JSON API** — PHP: `/api` + JSON. Frontend router: `/login`, `/app`, …. Two routers, API is frontend-agnostic, PHP is effectively M+C.

## Decision

**Option 3: SPA + JSON API.** Request/response and REST stay visible. Inertia only later via its own ADR, not as a hidden goal.

The overall system is still MVC (V in the frontend). Inside PHP: API with models and controllers, no HTML views as the main path.

## Consequences

- Page URLs and API URLs are different contracts, not 1:1.
- Auth must sit on the API; frontend guards are UX only.
- Vue and React are interchangeable (see ADR-0004).
- SPA fallback: non-API paths serve `index.html`.
