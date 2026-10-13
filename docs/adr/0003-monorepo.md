# ADR-0003: One repository, separate inits (Composer and npm)

- Status: Accepted
- Date: 2026-10-10

## Context

A clone should contain the skeleton (like after `laravel new` / `symfony new` / `create-next-app`), but two ecosystems are in play. Composer must **not** implicitly scaffold Vue/React — that would glue PHP init to the first UI kit and hide the cut from ADR-0002.

## Alternatives

1. Two repos — early overhead, contradicts “one pull”.
2. One repo, **one** super-init that runs Composer then `npm create vue` — convenient, couples the framework to one UI toolkit.
3. One repo, **two** inits: PHP skeleton via Composer (or a `create` command); frontend via npm/Vite in the frontend folder, separate on purpose.

## Decision

Option 3 was “two inits (Composer + npm)”. **Amended by [ADR-0012](0012-php-mvc-file-routing.md):** one repository, **Composer only** for the app. Views are PHP. No required `frontend/` or `npm` init.

- PHP: `composer install` / later `create-project` creates the PHP core.
- Composer does not run `npm` or Vue/React scaffolding.
- Locally: `composer serve` + Docker Postgres. Optional Tailwind CLI later is CSS tooling, not a second UI framework.

## Consequences

One clone + `composer install` is enough to run the app (plus Docker for Postgres). A JS SPA would need a new ADR.
