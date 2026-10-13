# ADR-0006: Frontend adapters — detect via package.json

- Status: Superseded by [ADR-0012](0012-php-mvc-file-routing.md)
- Date: 2026-10-10

## Context

ADR-0002 makes the API frontend-agnostic. ADR-0005 wants `make:page` only for real screens. Later **several** view stacks should be possible without binding PHP init (0003) to Vue.

Examples: TanStack Router (first adapter, ADR-0004), later Vue Router, React Router. The CLI should register routes **in that system** and detect what actually lives in the frontend folder — automatically or with a prompt.

This is a firm target, not a loose idea. Implement adapters **after** the first handmade frontend, not on day one.

## Alternatives

1. A hard-wired Vue scaffold in Composer — contradicts 0003 and blocks React/TanStack.
2. A config key `frontend.stack=vue` — clear, but duplicates `package.json`.
3. **Adapters:** read `package.json` (dependencies/devDependencies); pick the matching adapter; ask if unclear.

## Decision

Option 3. Scaffold scripts (`make:page`) talk to a **frontend adapter**, not to Vue directly.

Detection (rough order): dependencies in `frontend/package.json`, e.g.

- `@tanstack/react-router` → TanStack adapter (default target after 0004)
- `vue-router` → Vue adapter (later)
- `react-router` / `react-router-dom` → React Router adapter (optional)

One match: use the adapter (optional short confirmation). None or several: ask, do not guess. Unknown setup: abort with a hint to add the page by hand.

Each adapter knows where page files live and how a route is registered **in that router**. PHP routes under `/api` stay the same.

Nuxt/Next are their own apps; adapters only via a later ADR.

## Consequences

- PHP core and create stay stable while views grow.
- `make:api` needs no adapter; `make:page` does.
- First adapter = TanStack Router (0004); Vue after that; no PHP rewrite.
- False positives in `package.json` → confirmation is allowed.
