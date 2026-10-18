# Implementation plan

You build the core **phase by phase**. Companion: `AGENTS.md` (tasks and *why* until Go; code only on Go). Current model: **ADR-0012** (PHP views, file-based routes) plus **ADR-0016** (PageBuilder / layout). Vue/React are not the view.

**How to work a phase:** state the id (or the branch) → companion lays out the next tasks → you build the step → review → next step.

**Always on:** `declare(strict_types=1);`, namespaces, typed properties/parameters/returns, PDO only with `prepare`. English identifiers, comments, and docs.

Target PHP: **≥ 8.4** (ADR-0011).

```
public/router.php              Built-in-server hook (thin: file vs index.php)
public/index.php               Front controller (capture, resolve, run, send)
Framework/Http/                Request, Response
Framework/Core/                Config, Controller, Resolver
Framework/Page/                PageBuilder, MetaInfo
src/app/{path}/IndexClass.php  Page → GET /path (classmap)
src/views/{path}.php           Inner HTML for <main> (dots → folders)
src/layout/                    Document shell + header/footer partials
src/styles/                    SCSS → public/css/app.css
src/api/{path}/RouteClass.php  JSON → GET /api/path (ADR-0017; not wired)
scripts/make-route.php         composer make-route [-- --api later]
```

---

## Done

- **0.1** Environment: Composer, Postgres Compose, `.env`, `.gitignore`, `pdo_pgsql`.
- **0.2** OOP refresh.
- **Routing sketch:** `/test` → `src/app/test/IndexClass.php` → `run()`. `make-route`. Error HTML. Dotenv + `config()`.
- **1.1** Front controller vs dispatcher: `router.php` only “real file or app?”. `index.php` loads autoload and runs dispatch. `die()` gone.
- **1.2** Request and Response: `Request::capture()`, path from the Request, `run()` then `send()`. Resolver `false` → 404 (not `class_exists(false)`). Happy path: no `echo` in `IndexClass`. HTML error views via `catchException` until 3.1 (Error `IndexClass`).

---

## Wave 1 — HTTP and views (current)

**1.1** and **1.2** are done. HTML assembly (PageBuilder, ADR-0016) was started with 1.2; **1.3** is finishing that contract.

### Phase 1.3 — Controller, render, PageBuilder (in progress)

**Goal:** ADR-0014 + ADR-0016 (+ HTML rules in ADR-0013). `IndexClass extends Controller`. `run(): void` calls `render($view, $data): void`. `render` buffers the inner view, `PageBuilder` wraps `src/layout/page.php` + partials, `setBody`. Meta (title, css, js) via `MetaInfo`, not as layout keys inside view `$data`. Use `require`, not `require_once` / silent `include`.

**PHP focus:** inheritance, output buffering, careful `extract` (`EXTR_SKIP`).

**Already there:** base `Controller`, `Resolver` (route + view), `PageBuilder`, `MetaInfo`, `src/layout/*`, `src/views/test/index.php`, `/test` calls `render('test.index', …)`. Sass entry linked as `/css/app.css` (ADR-0015).

**Still to finish:**

- Typed `run(): void` on the base (or abstract); `declare(strict_types=1);` on every page class.
- Wire MetaInfo from the page (title per route) before `render`.
- PageBuilder must use the MetaInfo it was given (same instance as the Controller).
- Home `/` → `src/app/IndexClass.php` when you want `/` to work.
- **`response(): Response` getter** on the Controller so tests can `run()` and read status/body **without** `send()`.
- **Smoke test (now, not only 3.2):** PHPUnit as `require-dev`, `composer test`. One (or two) tests that boot Composer autoload — not the browser, not `index.php` (that would `send()`). Check `config('dir.views')` / `config('dir.layout')` exist as directories, `config('app.name')` is a non-empty string, Resolver maps `/test` to `IndexClass`, construct with `new Request(...)` (not `capture()`: CLI has no useful `$_SERVER` / `getallheaders()`), `run()`, assert 200 and HTML from layout + view. Optional: unknown path → Resolver `false`. No Postgres in this smoke.

**Do not:** Vue. Do not build `component()` / Table TS yet (ADR-0013, later). Nested per-folder `layout.php` would be a new ADR. Do not call `send()` in tests. Pest is optional later; start with PHPUnit so the first test is plain PHP.

### Phase 1.4 — Page `make-route` and classmap DX

**Goal:** Keep **classmap** for `src/app/` (lowercase folders; namespace `ucfirst`). Do not switch pages to PSR-4 — API will use the same rule (ADR-0017). Templates for `IndexClass` (`strict_types`, `extends Controller`, `run(): void`, Meta, `render`) plus matching view. Default: also an empty page SCSS under `src/styles/pages/…` and a matching `@use` in `src/styles/app.scss` (ADR-0015). `--no-scss` skips that. English stub text. Refuse overwrite without `--force`. After write: remind `composer dump-autoload`. Do not compile Sass from `make-route`.

**Done when:** `composer make-route foo` produces a loadable page + view; `/foo` can be opened; `src/styles/pages/foo/index.scss` exists and `app.scss` `@use`s it; the Dev only fills that SCSS and runs `composer assets`.

**Do not:** `--api` in this phase (`ApiController` does not exist yet). `--api` must never write SCSS.

### Phase 1.5 — JSON file routes (ADR-0017)

**Goal:** `Framework\Core\ApiController` (`json` helper, `send()`, no `render`). Resolver: `/api` and `/api/…` → `Api\…\RouteClass`. Glue unchanged besides `false` from resolve. Composer: **classmap** `src/api/`, drop PSR-4 `Api\` when you add that classmap. `make-route --api` writes `src/api/{path}/RouteClass.php` only (no view, no SCSS). Argument without a leading `api/`.

**Done when:** `GET /api/notes` hits the stub JSON; `GET /notes` is still a page if that page exists.

**Do not:** OpenAPI, `/api/v1`, a second `index.php`. JSON error bodies can wait for 3.1.

---

## Wave 2 — Persistence (ADR-0007)

### Phase 2.1 — PDO connection

**Goal:** One Connection from `config('db.*')`, DSN `pgsql:`, `ERRMODE_EXCEPTION`, prepared statements.

**Done when:** A page or `/health` can `SELECT 1`. Postgres down → HTML/JSON 500, no raw warning.

**Sources:** [PDO](https://www.php.net/manual/en/book.pdo.php)

### Phase 2.2 — First model + schema

**Goal:** Practice table (e.g. `notes`), not CMS. `storage/schema.sql`. `GET` list/show, `POST` create; `title` required → 422.

**Done when:** Row visible in Postgres; SQL only prepared.

---

## Wave 3 — Hardening

### Phase 3.1 — Errors

Typed exceptions (`NotFoundException`). Production hides traces (`APP_DEBUG`). Layout-friendly error views (same shell as pages, later).

### Phase 3.2 — Tests beyond smoke

The Wave-1 smoke (autoload, `config()`, `/test` via `run()` + `response()`) already exists. Here: more cases without the browser — 404/error page (3.1), later API JSON, POST/validation. PHPUnit stays the runner unless an ADR picks Pest.

---

## Wave 4 — DX, components, CSS

Harden `make-route` further if needed. `component()` helper (ADR-0013). Static PHP components first. Table + HTML-fragment endpoints after wave 2.

CSS pipeline **already exists** (ADR-0015): `src/styles/` → `public/css/app.css`. Remaining CSS work is authoring and keeping partials in step with `src/layout/`. Optional Vite later for `*.ts` → `public/build/` — still PHP HTML, not a SPA. Tailwind would need a new ADR.

**Do not:** `frontend/` Vue/React app.

---

## Wave 5 — Sessions and auth

File sessions, cookie flags. Then `users` + `password_hash` / `password_verify`. Protect writes.

---

## Wave 6 — CMS flavor (ADR-0009)

PHP partials as blocks, JSONB `modules`, admin as PHP pages. After auth.

---

## Wave 7 — Product

Sitemap/robots (0010), optional MySQL DSN, Composer create-project.

---

## Suggested next step

Finish leftover **1.3** (types, Meta on `/test`, `require`, `response()` getter, **smoke test**). Home `/` if you want it. Then **1.4** (page `make-route` + view + page SCSS). **1.5** after that. Error `IndexClass` is **3.1**.
