# ADR-0005: Scaffold — API always, page only on request

- Status: Superseded by [ADR-0012](0012-php-mvc-file-routing.md)
- Date: 2026-10-10

## Context

The goal is **developer experience**, not runtime coupling. Recurring creation of controller, route, and optionally a page should be a CLI job; after generate they are normal files.

Not a page per API route (no 1:1). Only **screens** (login, list, dashboard). Pure data/action endpoints (`GET /api/me`, `POST /api/login`, nested) stay API-only; the view is handmade.

Depends on ADR-0002, 0003, and 0006 (adapters). Which frontend first: ADR-0004.

## Alternatives

1. No generator — everything by hand (maximum learning value at the start).
2. Every API route creates a page — too many empty shells, false 1:1.
3. Two commands or a flag: API resource always; page only when it is a real screen route.
4. Inertia: the page route *is* the PHP route — different model, not here.

## Decision

The generator (built only after the core exists by hand) distinguishes:

| Command (working title) | PHP | Frontend |
|---|---|---|
| `make:api posts` | Controller, model, `/api/posts` | nothing |
| `make:page posts` | optionally the same API bits if missing | TanStack: route file in the routes folder (convention `/posts` → fetch `/api/posts`). Other adapters later (0006). |

`make:page` only for screens, not for `POST /api/login` or `/api/posts/{id}/comments`. The login **screen** is a page; login **submit** stays an API with no second page.

No templates under `public/`. Page files and router registration are written by the **current frontend adapter** (0006), not a hard-coded Vue template.

Convention, not a runtime bind: name `posts` → URL `/posts` + fetch `/api/posts`. If the page diverges, edit by hand after generate.

## Consequences

- Fewer leftover files; clear split between screen and endpoint.
- The script must know which frontend (0004) and that pages ≠ HTTP methods.
- Only worth it after you have built routing and one page without the generator.
