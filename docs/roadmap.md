# Implementation plan

You build the core **phase by phase**. Companion: `AGENTS.md`. Architecture: `docs/adr/` — **ADR-0012** is the current model (PHP views, file-based routes). Vue/React are **not** the view.

**How to work a phase:** state the id → skim → build it yourself → review → next phase.

**Always on:** `declare(strict_types=1);`, namespaces, typed properties/parameters/returns, PDO only with `prepare`. English identifiers, comments, and docs.

Target PHP: **≥ 8.4** (ADR-0011).

```
public/router.php              Dispatcher today (should thin out)
public/index.php               Intended front controller (not wired yet)
Framework/                     Kernel: Config, helpers, error views
src/app/{path}/IndexClass.php  Page controller (GET /path)
src/api/                       Future JSON controllers
scripts/make-route.php         composer make-route
```

---

## Done (toolbox + first routes)

- **0.1** Environment: Composer, Postgres Compose, `.env`, `.gitignore`, `ext-pdo_pgsql`.
- **0.2** OOP refresh.
- **File routing (sketch):** `/test` → `src/app/test/IndexClass.php` → `run()`. `composer make-route`. Error views 404/500. Dotenv + `config()`.

---

## Wave 1 — HTTP shape (from the current sketch)

### Phase 1.1 — Front controller vs dispatcher

**Goal:** `router.php` only: static file vs `index.php`. `index.php` loads autoload and starts the kernel. Stop using `public/index.php` as a debug `die()`.

**Done when:** `composer serve` + `GET /test` still works; `index.php` has no `die()`.

### Phase 1.2 — Request / Response

**Goal:** Stop scattering `$_SERVER` and raw `echo` for app pages. `Response` can still send HTML.

**Done when:** `IndexClass::run()` returns or uses `Response`; kernel sends it.

### Phase 1.3 — Base `Controller` + `render()` + layouts

**Goal:** Next.js-like: page class extends `Controller`; `render('view', $data)` wraps a layout. Error pages can use the same idea later.

**Done when:** `/test` uses a view file + layout, not only `echo "<h1>"`.

**Do not:** Vue, Tailwind as a blocker (optional after HTML works).

---

## Wave 2 — Persistence

Unchanged intent (ADR-0007): env already exists; add PDO connection, health or a first model, `storage/schema.sql`. Prepared statements only.

---

## Wave 3 — Hardening

JSON-or-HTML error envelope as needed, middleware if you need it, PHPUnit against the dispatcher without a browser.

---

## Wave 4 — DX

Harden `make-route` (English output, `strict_types` in the stub, extend base `Controller`). Optional Tailwind scan of `*.php` for CSS — still PHP views.

**Do not:** npm Vue app, TanStack, `frontend/` as the UI.

---

## Wave 5 — Sessions and auth

File sessions, then `users` + `password_hash`. Protect writes.

---

## Wave 6 — CMS flavor (ADR-0009)

PHP partials as blocks, JSONB `modules`, admin as PHP pages. No Sanity, no Vue block catalogue unless a new ADR.

---

## Wave 7 — Product

Sitemap/robots (0010), MySQL DSN later (0007), Composer create-project.

---

## Suggested next step

**Phase 1.1** — split `router.php` / `index.php`, keep file-based `/test`. Then 1.3 (base Controller, render, layouts) is the Next.js-shaped piece you want.
