# ADR-0012: PHP-only MVC, file-based routing, PHP views (no Vue/SPA as the view)

- Status: Accepted
- Date: 2026-10-13
- Supersedes: ADR-0002 (view coupling), ADR-0004 (first JS frontend), ADR-0005 (`make:page` for SPA), ADR-0006 (JS frontend adapters)
- Amended by: [ADR-0017](0017-api-file-routes.md) (JSON file routes: `/api/…`, `RouteClass`)

## Context

The earlier target was a JSON API plus a React/TanStack (later Vue) SPA. Implementation went another way: a **PHP MVC** inspired by Next.js **file-based routing**. Vue/React are not the view layer. HTML comes from PHP (`echo` today; layouts + `render` next). Tailwind may style that HTML later (CDN or a CSS build that scans `.php`).

Code this ADR describes (snapshot):

- `public/router.php` — PHP built-in-server hook **and** the dispatcher (not a thin `index.php` yet).
- URL path → folder under `src/app/` → `IndexClass.php` → `run()`.
- Example: `GET /test` → `src/app/test/IndexClass.php`, namespace `App\Controllers\Test`.
- `composer make-route` / `scripts/make-route.php` scaffolds that file.
- Error HTML: `Framework/Views/errors/{404,500}.php`.
- PSR-4: `Framework\` → `Framework/`, `App\` → `src/app/`, `Api\` → `src/api/`.
- Snapshot at write time: dispatcher still mixed. Later: thin `router.php`, `index.php` glue, base `Controller`, `render()`, layout via PageBuilder (ADR-0014, ADR-0016).

## Alternatives

1. Keep SPA + JSON (ADR-0002) — dropped for now so the PHP kernel and HTML views can be learned in one language.
2. Inertia — still not the path.
3. **PHP MVC + file-based routes + PHP views/layouts.** Optional JSON under `src/api/` later, same kernel.

## Decision

Option 3.

- **View = PHP.** No Vue/Nuxt/React/TanStack as the page renderer. No `frontend/` npm app as the main UI.
- **Routing = files.** One folder segment = one URL segment. HTML class name: `IndexClass`, method `run()`. JSON: **ADR-0017** (`/api/…`, `RouteClass`, `ApiController`).
- Controllers should extend a shared base **Controller**. That base owns **render** (view + data) and **layouts** (wrap content, Next.js-like).
- `src/api/` is JSON file routes (ADR-0017), not the homepage renderer.
- Composer does not install npm or Vue. Tailwind, if used, is CSS for PHP-rendered HTML.
- Next.js is a **shape** (folders, layouts, a page module per route), not a Node runtime.

## Mapping (Next.js → this repo)

| Next.js App Router | This framework |
|---|---|
| `app/page.tsx` | `src/app/IndexClass.php` (home `/`) |
| `app/test/page.tsx` | `src/app/test/IndexClass.php` (`/test`) |
| `app/api/…/route.ts` | `src/api/…/RouteClass.php` (`/api/…`, ADR-0017) |
| default export / server render | `IndexClass::run()` / `RouteClass::run()` |
| `layout.tsx` | `src/layout/` + `PageBuilder` + `Controller::render()` (ADR-0016) |
| `not-found.tsx` | `Framework/Views/errors/404.php` |

## Consequences

- SEO/a11y (ADR-0010) get easier: first response can be HTML, not an empty SPA shell.
- CMS flavor (ADR-0009) would compose **PHP partials/blocks**, not Vue/React components, unless a later ADR brings a JS admin back.
- Two routers in the old sense are gone: PHP chooses both the page and the HTML.
- `public/index.php` should become the HTTP entry; `router.php` should only decide static file vs `index.php` (today they are mixed).
