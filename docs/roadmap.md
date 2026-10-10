# Implementation plan

You build the core **phase by phase**. The agent is a companion (review, explanation, next bite) — see `AGENTS.md`. Architecture: `docs/adr/`.

**How to work a phase:** state the id → skim (PHP focus + sources) → build it yourself → ask the companion for review → only then the next phase. Do not pull the generator or CMS forward.

**Always on:** `declare(strict_types=1);`, namespaces, typed properties/parameters/returns, PDO only with `prepare`. English identifiers, comments, and docs.

Target PHP: **≥ 8.4** (ADR-0011).

```
public/                 Front controller (only web root)
src/Framework/          Kernel (Http, Routing, Database, …)
app/                    Your app (controllers, models)
config/  routes/  storage/
frontend/               from wave 4 (React + TanStack)
```

---

## Wave 0 — Toolbox

### Phase 0.1 — Environment

**Goal:** PHP 8.4+, Composer, Git, Docker. Postgres container starts; PHP may run **on the host** first (`php -S`) if `pdo_pgsql` is present.

**PHP focus:** Check version (`php -v`, `php -m` includes `pdo_pgsql`). No OOP requirement yet.

**You:** Git init if needed. `.gitignore` (`/vendor`, `.env`, `node_modules`, IDE, `.obsidian`). `.env.example` with Postgres placeholders. `docker-compose.yml` for Postgres only, credentials from `.env`. `composer.json` with PSR-4 `Framework\\` → `src/Framework/`, `App\\` → `app/`, script `serve` → `php -S localhost:8000 -t public public/router.php` (router file in 1.1).

**Done when:** `docker compose up -d` and a client can connect; `composer dump-autoload` works; no secrets in Git.

**Sources:** [php.net/install](https://www.php.net/manual/en/install.php) · [getcomposer.org](https://getcomposer.org) · [PDO PostgreSQL](https://www.php.net/manual/en/ref.pdo-pgsql.php)

**Do not:** frontend, install Laravel, PHP-FPM cluster.

### Phase 0.2 — OOP refresh (reading, little code)

**Goal:** See the language you need from 1.1 onward once.

**PHP focus:** Class, object, `public`/`private`/`protected`, constructor property promotion, `readonly`, namespaces + `use`, `declare(strict_types=1)` (otherwise `int` hints coerce). Optional: `enum`, `match`.

**Done when:** You can explain promotion vs classic properties, and why `strict_types` must sit in *every* file (it is per-file).

**Sources:** [Classes](https://www.php.net/manual/en/language.oop5.php) · [Namespaces](https://www.php.net/manual/en/language.namespaces.php) · [strict_types](https://www.php.net/manual/en/language.types.declarations.php#language.types.declarations.strict) · [Constructor promotion](https://www.php.net/manual/en/language.oop5.decon.php#language.oop5.decon.constructor.promotion) · [Readonly](https://www.php.net/manual/en/language.oop5.properties.php#language.oop5.properties.readonly-properties)

---

## Wave 1 — HTTP core (no database)

### Phase 1.1 — Front controller + autoload

**Goal:** One HTTP entry. No logic in ten PHP files under the URL.

**PHP focus:** `public/index.php` without a namespace (entry). Everything else under `Framework\`. Autoload vs `require`.

**You:** `public/index.php` loads `vendor/autoload.php`, starts `Framework\Application`. `public/router.php` for the built-in server: serve an existing file, otherwise `index.php`. Application may at first only `echo` JSON `{"ok":true}` — thin on purpose.

**Done when:** `composer serve` (or `php -S …`) → `GET /` returns JSON, status 200.

**Sources:** Front controller (concept) · Composer PSR-4

**Do not:** Full router in the same session as “hello JSON” if you overload — prefer 1.2.

### Phase 1.2 — Request and Response

**Goal:** `$_GET`/`$_SERVER`/`php://input` not scattered. The answer is an object that `send()`s.

**PHP focus:** `readonly` properties, `self`/`static` factory `Request::capture()`, return types `array`, `string`, `int`, `never` on `send()` (terminates). JSON: `json_encode` + header `Content-Type: application/json`.

**You:** `Framework\Http\Request` (`method()`, `path()`, `query()`, `json()` from `php://input`), `Framework\Http\Response` with `json()`, `status()`, `header()`. Application: build Request, send Response. Empty body from `json()` may be `[]`/`null`; invalid JSON should throw rather than silently `[]`.

**Done when:** You can set status 201 and a JSON body without `echo` in Application.

**Sources:** [Superglobals](https://www.php.net/manual/en/language.variables.superglobals.php) · [json_encode](https://www.php.net/json-encode)

### Phase 1.3 — Router

**Goal:** Method + path → callable. `{id}` parameters. 404 / 405.

**PHP focus:** `enum HttpMethod: string`. `match` for method dispatch if it helps. Regex vs simple segment maps — pick what you can explain. No magic.

**You:** `GET /api/health` → closure or `[Class, 'method']` that returns JSON. No DB ping yet.

**Done when:** Unknown URL → 404 JSON; `POST /api/health` when only GET is registered → 405; `{id}` arrives as an argument or request attribute.

**Sources:** [Enums](https://www.php.net/manual/en/language.enumerations.php) · [match](https://www.php.net/manual/en/control-structures.match.php)

**Do not:** Middleware pipeline, attribute routing (can come later).

### Phase 1.4 — Controller

**Goal:** App code in `App\Controllers\…`, kernel in `Framework\`.

**PHP focus:** Class methods with typed `Request $request`: `Response`. Instantiate simply (`new`) first; a container only when you really need dependencies.

**You:** `routes/api.php` registers `HealthController::show`. Controller `return Response::json([...])`.

**Done when:** A new API method = a new controller method + one route line; kernel unchanged.

---

## Wave 2 — Persistence

### Phase 2.1 — Env and config

**Goal:** DSN, user, password not in PHP source.

**PHP focus:** `private` parser, `static` only where config is truly global — or a `Config` object from Application. No `extract()`.

**You:** Load `.env` (yourself or a **small** lib you understand). `config/database.php` returns an array for PDO. Never commit `.env`.

**Done when:** Host/port/database/name/password change without touching PHP classes.

**Sources:** [putenv/getenv](https://www.php.net/manual/en/function.getenv.php) — or dotenv docs if you take a lib.

### Phase 2.2 — PDO connection

**Goal:** One Connection class, DSN `pgsql:…`, `ERRMODE_EXCEPTION`, prepared statements.

**PHP focus:** Dependency: Application holds `PDO` or `Connection`. Exceptions, not `or die()`.

**You:** `GET /api/health` runs `SELECT 1` (ping). Failure → JSON 500, no HTML warning.

**Done when:** Compose Postgres down → clear error; up → health ok. No interpolated SQL strings anywhere.

**Sources:** [PDO](https://www.php.net/manual/en/book.pdo.php) · [PDO::prepare](https://www.php.net/manual/en/pdo.prepare.php) · [Connections](https://www.php.net/manual/en/pdo.connections.php)

### Phase 2.3 — First schema + model

**Goal:** A practice resource (e.g. `notes`: id, title, body, timestamps) — **not** the CMS.

**PHP focus:** Model as a class around a table, typed getters or readonly DTOs after `fetch`. `declare(strict_types=1)` + turn PDO `FETCH` arrays into objects (casts on purpose).

**You:** SQL file `storage/schema.sql` (baby migration; a real migration system = later ADR). Apply by hand or a tiny CLI. `GET/POST /api/notes`, `GET /api/notes/{id}`. POST: JSON body, `title` required — otherwise 422 JSON, no silent empty insert.

**Done when:** Create and read a note over HTTP, row visible in Postgres, SQL only prepared.

**Do not:** Page builder, user auth, file upload.

---

## Wave 3 — Hardening

### Phase 3.1 — Errors and JSON consistency

**Goal:** Uncaught exceptions and 404 look the same (`{ "error": "…", "status": n }`), no stack trace in production (`APP_DEBUG`).

**PHP focus:** `Throwable`, `try/catch` in the kernel, own exception classes (`NotFoundException`) with typed properties.

### Phase 3.2 — Middleware

**Goal:** Pipeline: Request in, Response out. CORS for Vite (`localhost:5173`) before wave 4. Optional JSON-only for `/api`.

**PHP focus:** Interface `MiddlewareInterface`, callable chain. No global flags.

### Phase 3.3 — First tests

**Goal:** PHPUnit (or Pest if you can read it) against router/controller **without** a browser. Serious framework (0008).

**PHP focus:** Autoload `tests/`, namespace `Tests\`.

**Done when:** One test for 404, one for health or notes — `composer test` green.

**Do not:** Force 90% coverage. Tests that need the real Docker DB should be labelled or wait for CI; kernel tests first without the network.

---

## Wave 4 — First SPA (by hand)

### Phase 4.1 — Vite + React + TanStack Router

**Goal:** Own `npm` init in `frontend/` (ADR-0003: Composer does not touch it). One route `/notes`, `fetch('/api/notes')`.

**PHP focus:** none — except CORS/proxy.

**You:** Vite dev proxy `/api` → `localhost:8000`. File-based routes the TanStack way.

**Done when:** Notes from Postgres show on the page, no PHP HTML.

**Sources:** Vite · TanStack Router file-based routing · `fetch` + JSON

**Do not:** `make:page`, admin CMS, Start/SSR.

### Phase 4.2 — SPA fallback

**Goal:** Reload of `/notes` in production: PHP serves the built `index.html`, not 404. `/api/*` stays the API.

**Done when:** `npm run build` into `public/…`, PHP distinguishes file / API / index.

---

## Wave 5 — DX (only after one handmade page)

### Phase 5.1 — `make:api`

CLI in the PHP core: model/controller/route stub for a resource. Templates that include **strict_types** and namespaces.

### Phase 5.2 — `make:page` + TanStack adapter

Route file in the frontend folder (ADR-0005/0006). `package.json` detects `@tanstack/react-router`.

**Do not:** Vue adapter or React Router before this adapter works.

---

## Wave 6 — Sessions and auth

### Phase 6.1 — PHP sessions (files)

Test login state without a users table (e.g. dummy). Cookie flags (`HttpOnly`, `SameSite`, later `Secure`). Sketch CSRF for cookie auth (ADR when you choose: session cookie vs token).

### Phase 6.2 — Users in Postgres

Table `users`, password hash (`password_hash` / `password_verify`, **not** MD5). `POST /api/login`, `GET /api/me`, logout. Writes on notes only when logged in.

**Sources:** [password_hash](https://www.php.net/manual/en/function.password-hash.php) · session security

**Do not:** OAuth, Firebase Auth, CMS role model.

---

## Wave 7 — CMS flavor (ADR-0009)

Only after 6.2.

1. Table `pages` (slug, title, status, `modules` JSONB).
2. `GET /api/pages/{slug}` public.
3. Writes only with auth.
4. One TanStack catch-all + `ModuleRenderer` (one block type is enough, e.g. `text`).
5. Mini admin: create a page, set one module.
6. Optional `make:block` later.

A11y in **your** blocks (0010). No Sanity.

---

## Wave 8 — Product and packaging

- Sitemap + `robots.txt` from public slugs (0010).
- MySQL driver as a second PDO DSN (0007).
- Create-project: Composer package from *this* skeleton; init menu Compose vs external DB (0003/0007).
- SSR/Start only with its own ADR, no silent rewrite of 0004.

---

## Suggested next three steps

1. Phase 0.1 (Docker + Composer)
2. Phase 0.2 (read OOP)
3. Phase 1.1 (JSON `{"ok":true}` from the front controller)

CMS, scaffold, auth: not in these three.
