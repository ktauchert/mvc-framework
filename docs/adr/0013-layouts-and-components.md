# ADR-0013: Master layout, views into `<main>`, PHP components (HTML fragments, optional TS)

- Status: Accepted
- Date: 2026-10-13
- Depends on: ADR-0012 (PHP views, file-based routes), ADR-0010 (meta/a11y)

## Context

Pages must not repeat `<html>`, `<head>`, header, footer. A **master layout** wraps **one content view** into `<main>`. Title and later meta/theme live in the layout and are overridable per page.

Independently, UI pieces (Button, Table) should be reusable **components**: PHP template plus optional TypeScript for fetch (pagination, filter, sort, optional row actions). That is progressive enhancement, not Vue/React as the view (0012).

The other-chat sketches used `extract()` + `ob_start` + `require_once`. The layout idea is sound; `require_once` and dual JSON+HTML row rendering are not.

## Decision

**Hard rules (easy to miss in jolly sketches):**

- Views, layouts, and components: **`require`**, never **`require_once`**.
- Do not `extract()` blindly; if you do, `EXTR_SKIP`. Never a prop named `content` (layout slot).
- Escape output (`htmlspecialchars`) unless the value is already trusted HTML.
- Table updates: **HTML fragments from PHP**, not a second row renderer in TypeScript.
- `component()` / Table TS only **after** phase 1.3 (`render` + layout).

### 1. Layout + view (phase 1.3)

- Default layout: `src/views/layouts/main.php` — once: doctype, `<head>`, header, `<main><?= $content ?></main>`, footer, script/style tags.
- Page views: `src/views/{name}.php` — **only** inner HTML for `<main>` (no second document).
- `Framework\Core\Controller::render(string $view, array $data = [])`:
  1. Buffer the view (`ob_start` / `ob_get_clean`) → `$content`.
  2. Require the layout (same request). Layout sees `$content` plus page data (`$title`, later `$meta`).
- Use **`require`**, not `require_once`, for views, layouts, and components. `require_once` skips a second include of the same file (two Buttons on one page would break).
- Prefer not to `extract()` blindly; if used, `EXTR_SKIP` and never pass a prop named `content` (clashes with the slot).
- **Title / meta:** `$data['title']` in `<title>`. Later `$data['meta']` (description, canonical, JSON-LD) in `<head>` without a new layout file per page.
- **Theme / other layouts:** `render($view, $data, $layout = 'main')` later (e.g. `admin`, `auth`) or a theme prefix. Not required for the first layout.

File-based `IndexClass::run()` calls `$this->render(...)`. Error views may keep a dedicated layout or the same `main` later.

### 2. Components (after `render()` works)

- Path: `src/components/{ui/Table}/Table.php` (+ optional `table.ts`).
- Helper `component('ui.Table', array $props): string` — dots → folders, output buffering, return HTML. Missing `.ts` is fine (static component).
- **Hydration contract:** JSON **or** HTML fragments for updates — **not both** for the same Table. **HTML fragments:** row/table partials stay PHP; TS shows a loader, `fetch`es, swaps DOM (`innerHTML` / `outerHTML`). JSON list APIs may exist under `src/api/` for other clients, not as a second row template in TS.
- List endpoints (later, after PDO): query params for page, sort, filter (column **whitelist**). Row CRUD is **optional props/actions**, not mandatory Table behaviour.
- TS bundling is an **asset pipeline** (e.g. Vite → `public/build/`), loaded from the layout. That is not a SPA and does not revive ADR-0002.
- `extract` in components: same caution as views. Escape output (`htmlspecialchars`). Several Tables on one page: unique `id` + `data-endpoint`.

### 3. What not to do now

Do not implement `component()` or Table-TS before phase **1.3** (layout + `render`). Do not add Vue. Do not run `apt`/Composer to fetch npm as part of PHP init (0003).

## Consequences

- One HTML shell, pages stay small, SEO/meta have a single `<head>` (0010).
- Components compose inside views; views compose into the layout.
- Implementation order: 1.1 front controller → 1.3 `Controller::render` + `main` layout → `component()` helper → static components → Table + fragment endpoint after wave 2 → Vite when TS is needed.
