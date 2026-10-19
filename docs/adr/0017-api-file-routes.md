# ADR-0017: File-based JSON API — `/api/…`, `RouteClass`, `ApiController`, classmap

- Status: Accepted
- Date: 2026-10-16
- Depends on: ADR-0012 (file routes), ADR-0014 (`run` / `send`, one front controller)
- Amends: ADR-0012 (`src/api/` was only “later JSON”); ADR-0014 (“API IndexClass”) — the API file is **`RouteClass`**, not `IndexClass`
- Amended by: [ADR-0018](0018-mvc-cli.md) (`php mvc make:api` replaces `make-route --api`)

## Context

HTML pages are folders under `src/app/` with **`IndexClass.php`**. JSON should use the same *shape* (one folder = one URL segment) without sharing the page base (`Controller`, `render`, MetaInfo, layout).

Next.js splits **page** vs **route handler**. Here that split is visible on disk: `IndexClass` vs `RouteClass`. URLs stay disjoint: a page is `/notes`, JSON is **`/api/notes`**.

Composer today maps `Api\` → `src/api/` as PSR-4. Pages under `src/app/` already use a **classmap** and lowercase folders (`src/app/test/`). Two autoload styles for two file trees would punish Linux (PSR-4 needs `Notes/RouteClass.php`; pages keep `test/IndexClass.php`). One rule for both app trees is simpler.

## Alternatives

1. **PSR-4 only for `src/api/`** — correct Composer, PascalCase folders, unlike `src/app/`.
2. **Same `IndexClass` name in `Api\`** — less Resolver branching, weaker signal when opening a file.
3. **JSON as a page without a view (`--no-view`)** — still a `Controller`, still `/notes`, easy to mix HTML and JSON.
4. **Prefix `/api/`, `RouteClass`, `ApiController`, classmap like `src/app/`.**

## Decision

Option 4.

### URL and files

| HTTP | File | Class |
|---|---|---|
| `GET /notes` | `src/app/notes/IndexClass.php` | `App\Controllers\Notes\IndexClass` |
| `GET /api/notes` | `src/api/notes/RouteClass.php` | `Api\Notes\RouteClass` |
| `GET /api` | `src/api/RouteClass.php` | `Api\RouteClass` |

- The first URL segment **`api`** selects the API tree. It is not a folder under `src/app/`.
- Remaining segments map like pages: lowercase directories, namespace parts `ucfirst`.
- **No view** for API routes. `php mvc make:api` must not write `src/views/` or SCSS.

### Bases

- **`IndexClass` extends `Controller`** — HTML: `render`, PageBuilder, MetaInfo (ADR-0016).
- **`RouteClass` extends `ApiController`** — JSON: Request + Response, a `json(...)` (or equivalent) body helper, **`send()`**. No `render`, no layout.

Both keep **`run(): void`**. Glue stays **one** `public/index.php`: capture → resolve → `new` → `run()` → `send()` (ADR-0014). Not a second front controller.

`ApiController` does not extend `Controller`. Shared bits later (if any) would be a small HTTP holder — not required to start.

### Autoload

`src/app/` and `src/api/` are both **classmap** (same as today’s pages). `Framework\` stays PSR-4.

New files need `composer dump-autoload` until a later DX wrapper does it. Folders stay lowercase; classmap does not require the directory name to match the namespace segment.

Composer’s current `"Api\\": "src/api/"` PSR-4 entry is **replaced by classmap** when this is implemented — not left as a second mapping for the same tree.

### Resolver and glue (not built yet)

`Resolver` (or a sibling) must:

1. If the path is `/api` or starts with `/api/` → build `Api\…\RouteClass` (strip the `api` segment).
2. Else → today’s `App\Controllers\…\IndexClass`.
3. Missing class → `false` / throw 404. Do not pass `false` into `class_exists`.

Glue does not care which tree it got, as long as the instance has `run()` and `send()`. Errors for API should become JSON later (wave 3 can split HTML vs JSON error answers). Until then, HTML error views on an `/api/` URL are acceptable but not the end state.

### Scaffold (ADR-0018)

```bash
php mvc make:page notes    # page: IndexClass + view (+ SCSS unless --no-scss)
php mvc make:api notes     # JSON: src/api/notes/RouteClass.php, no view, no SCSS
```

The `make:api` argument is the resource path **without** a leading `api/`. The command adds the URL prefix and the `src/api/` tree. Do not use `--api` or `--no-view` on `make:page`.

Implement **page** scaffolding first (`make:page`). `make:api` only after `ApiController` exists (phase 1.5).

### What this is not

- Not a JS SPA consuming a public JSON API as the page renderer (ADR-0012).
- Not OpenAPI, versioning (`/api/v1`), or REST resource conventions in this ADR.
- Not nested Next.js `route.ts` HTTP method files; one `RouteClass::run()` may `match` on `$this->request` method later.

## Consequences

- Opening a file tells you the kind: `IndexClass` = HTML page, `RouteClass` = JSON endpoint.
- `/notes` and `/api/notes` can coexist.
- Wave 1 stays HTML until `ApiController` + Resolver branch land (roadmap phase after `make:page`).
- Tests can `run()` an API class and read JSON from the Response without `send()`, same as pages.
