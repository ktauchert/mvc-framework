# ADR-0004: Which frontend first?

- Status: Superseded by [ADR-0012](0012-php-mvc-file-routing.md)
- Date: 2026-10-10

## Context

PHP is decided (API, JSON, separate init). This is only the **first** view: learn it by hand, then the first `make:page` adapter (0006). Other stacks (including Vue) stay allowed.

React is familiar water; Vue should still happen later — as a second adapter, not as the start.

## What this decision is not

- Not PHP architecture (0002).
- Not “React forever” (0006).
- Not Composer installing the frontend (0003).

## Alternatives

1. Vue 3 + Vite + Vue Router
2. React + Vite + React Router
3. Nuxt or Next first (too heavy, not the start)
4. **React + Vite + TanStack Router** (file-based routes: a new page is a file in the routes folder)

## Decision

**Option 4.** First frontend: React with Vite and **TanStack Router**, as an **SPA** against the PHP API. TanStack Start (SSR) is deliberately not the first pass; the router *can* SSR later, see ADR-0010.

`make:page` mainly writes a file into the routes folder; the router picks it up by convention. Meeting Vue: a later adapter.

## Consequences

- First 0006 adapter = TanStack Router (React).
- `make:page posts` ≈ add a route file, no central `routes.ts` patch.
- PHP `/api` unchanged.
- Vue remains learning goal #2, not blocked.
