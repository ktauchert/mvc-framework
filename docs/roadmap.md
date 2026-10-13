# Implementation plan

You build the core **phase by phase**. Companion: `AGENTS.md`. Current model: **ADR-0012** (PHP views, file-based routes). Vue/React are not the view.

**How to work a phase:** state the id → skim (PHP focus + sources) → build it yourself → companion review → next phase.

**Always on:** `declare(strict_types=1);`, namespaces, typed properties/parameters/returns, PDO only with `prepare`. English identifiers, comments, and docs.

Target PHP: **≥ 8.4** (ADR-0011).

```
public/router.php              Built-in-server hook (thin: file vs index.php)
public/index.php               Front controller (autoload + dispatch)
Framework/                     Kernel: Config, Controller, Views, helpers
src/app/{path}/IndexClass.php  Page controller → GET /path
src/api/                       Future JSON
scripts/make-route.php         composer make-route
```

---

## Done

- **0.1** Environment: Composer, Postgres Compose, `.env`, `.gitignore`, `pdo_pgsql`.
- **0.2** OOP refresh.
- **Routing sketch:** `/test` → `src/app/test/IndexClass.php` → `run()`. `make-route`. Error HTML. Dotenv + `config()`. Dispatcher still lives in `router.php`; `index.php` is a debug `die()`.

---

## Wave 1 — HTTP and views (on the current sketch)

### Phase 1.1 — Front controller vs dispatcher

**Goal:** Same split we discussed: `router.php` only asks “real file or app?”. `index.php` loads autoload and runs dispatch. Remove `die()` from `index.php`.

**PHP focus:** `require` vs autoload; one entry script without a namespace.

**Done when:** `composer serve`, `GET /test` still works; requesting a missing CSS file 404s as a file, app URLs still hit `IndexClass`.

**Do not:** layouts in the same sitting if you overload — that is 1.3.

### Phase 1.2 — Request and Response

**Goal:** `$_SERVER` / `echo` not scattered. `Request` from globals; `Response` with status, headers, HTML body (JSON later for `src/api/`).

**PHP focus:** `readonly`, `Request::capture()`, `never` on `send()` if you terminate there.

**Done when:** `run()` returns a `Response` (or the kernel sends one); no raw `echo` in `IndexClass` for the happy path.

**Sources:** [Superglobals](https://www.php.net/manual/en/language.variables.superglobals.php)

### Phase 1.3 — Base Controller, render, layouts

**Goal:** ADR-0013: `IndexClass extends Controller`. `render($view, $data)` buffers `src/views/{view}.php` into `$content` and requires `src/views/layouts/main.php`. Title via `$data['title']`. Use `require`, not `require_once`.

**PHP focus:** inheritance, output buffering, careful `extract` (`EXTR_SKIP`).

**Done when:** `/test` is a view + layout, not `echo "<h1>"`. Home `/` has `src/app/IndexClass.php` if you want `/` to work.

**Do not:** Vue. Do not build `component()` / Table TS yet (same ADR, later). Tailwind optional after HTML works.

### Phase 1.4 — Align namespace, autoload, and folders

**Goal:** PSR-4 path matches namespace (today `App\Controllers\Test` vs file `src/app/test/` is inconsistent). `make-route` emits `strict_types` and `extends Controller`. English stub text.

**Done when:** Autoload can load `IndexClass` without a manual `require_once` of the file, or you document why `require` stays. `composer make-route foo` produces a valid page class.

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

Typed exceptions (`NotFoundException`). Production hides traces (`APP_DEBUG`). Layout-friendly error views.

### Phase 3.2 — Tests

PHPUnit (or Pest) against dispatch without the browser. One 404, one `/test` or health.

---

## Wave 4 — DX, components, CSS

Harden `make-route`. `component()` helper (ADR-0013). Static PHP components first. Table + HTML-fragment endpoints after wave 2. Optional Vite/Tailwind for `*.ts` / `*.php` → `public/build/` — still PHP HTML, not a SPA.

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

**Phase 1.1** — split `router.php` / `index.php`. Then **1.3** (Controller + render + layouts) for the Next.js-like pages. **1.2** can sit between if `echo` starts to hurt.
