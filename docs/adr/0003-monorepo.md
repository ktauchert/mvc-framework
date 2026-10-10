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

Option 3. One repository. PHP init and Node init stay separate.

- PHP: `composer install` / later `create-project` (or CLI `new`) creates only the PHP core.
- Frontend: `npm create` / `npm install` in the frontend folder when the view is due.
- Composer does not run `npm` or Vue/React scaffolding.
- Locally two dev processes; production: PHP document root plus the built bundle in `public/`.

Concrete folder names when the first layer is built. Frontend detection and page scaffold: ADR-0006.

## Consequences

One clone is enough for the repo; running it needs two installers. The first frontend (0004) is independent of PHP create. Later frontends do not require rewriting PHP init.
