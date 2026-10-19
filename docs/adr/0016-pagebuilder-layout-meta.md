# ADR-0016: Page assembly — PageBuilder, layout shell, MetaInfo, dotted views

- Status: Accepted
- Date: 2026-10-16
- Depends on: ADR-0013 (layout + view HTML rules), ADR-0014 (`run` / `render` / `send`), ADR-0015 (CSS entry)
- Amends: ADR-0013 file locations (`src/views/layouts/main.php`, title only via `$data['title']`); ADR-0014 “render buffers view then layout” as a single include chain

## Context

ADR-0013 sketched one master file `src/views/layouts/main.php` that both owns `<html>`/`<head>` and inlines header/footer. While implementing Request/Response and `Controller::render` (wave 1), a split was clearer:

- The **document shell** (doctype, `<head>`, slots for header / main / footer) is stable.
- **Header and footer** change independently of the shell.
- **Title, description, CSS/JS URLs** are page metadata, not view locals mixed into `$data` for `<main>`.
- Inner views should nest like routes (`test.index` → `src/views/test/index.php`), not a flat `src/views/test.php`.

## Alternatives

1. **Keep ADR-0013 literally** — one `main.php` under `src/views/layouts/`; `$data['title']` into `<title>`.
2. **Next.js nested `layout.php` per folder** — powerful, too much for the first HTML shell.
3. **PageBuilder + MetaInfo + `src/layout/`** — Controller still calls `render`; HTML wrap is a dedicated type.

## Decision

Option 3.

### Files

| Path | Role |
|---|---|
| `src/layout/page.php` | Document shell: `<html>`, `<head>` from MetaInfo, `<header>` / `<main>` / `<footer>` slots |
| `src/layout/partials/_header.php` | Default site header (nav) |
| `src/layout/partials/_footer.php` | Default site footer |
| `src/views/{segments}.php` | Inner HTML for `<main>` only |
| `Framework/Page/PageBuilder.php` | Buffer header + footer partials, include the shell, return the HTML string |
| `Framework/Page/MetaInfo.php` | Title, description, keywords, author, favicon, CSS URL, JS URL |
| `src/styles/main/` | SCSS for that shell (compiled via ADR-0015 into `/css/app.css`) |

`config('dir.layout')` and `config('dir.views')` point at those trees.

### Object lifetime (happy path)

Unchanged from ADR-0014, with one extra owner on the Controller:

1. Glue constructs `IndexClass($request)` → base Controller stores Request, `new Response()`, `MetaInfo`, `PageBuilder`.
2. `run(): void` may adjust MetaInfo (title, …) and **must** call `$this->render($view, $data)`.
3. `render`:
   - Resolves the view path (`Resolver`, dots → folders, suffix `.php`).
   - Buffers the view. `$data` is **view locals** for `<main>` (`EXTR_SKIP`). Do not put layout slots in `$data` (no key `content`).
   - `$this->pageBuilder->buildFullPage($content)` includes header/footer partials and `page.php`.
   - `setBody` on the inner Response.
4. Glue calls `send()`.

The page author still never builds HTTP. They do not include `page.php` themselves.

### View names

Dotted names map to files under `src/views/`:

- `test.index` → `src/views/test/index.php` (page `/test`)
- later `notes.show` → `src/views/notes/show.php`

`Resolver::resolveView` throws if the file is missing (that is a 500-style failure, not a missing route).

### What ADR-0013 still owns

- `require` (not `require_once`) for views, layout shell, and partials.
- Escape unless the value is trusted HTML (`$content` from our buffer is trusted HTML).
- `component()` / Table TS still **after** this layout works.
- Optional extra layouts (`admin`, `auth`) stay later; this ADR only names the default shell.

### CSS

The shell links **one** stylesheet: MetaInfo default `/css/app.css` (ADR-0015). Do not glob `public/css/`.

## Consequences

- `render()` stays the HTML step (ADR-0014); PageBuilder is how that HTML is assembled.
- Header/footer/SCSS can evolve without rewriting the document shell.
- Glue, Request, Response, and `run`/`send` do not change because of this ADR.
- Tests can still `run()` and read the Response body without `send()`.
- Nested folder layouts (per-route `layout.php`) would need a new ADR.
