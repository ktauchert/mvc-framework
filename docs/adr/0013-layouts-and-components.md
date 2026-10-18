# ADR-0013: Master layout, views into `<main>`, PHP components (HTML fragments, optional TS)

- Status: Accepted
- Date: 2026-10-13
- Depends on: ADR-0012 (PHP views, file-based routes), ADR-0010 (meta/a11y)
- Amended by: [ADR-0016](0016-pagebuilder-layout-meta.md) (PageBuilder, `src/layout/`, MetaInfo, dotted views)

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

**Current files and assembly:** [ADR-0016](0016-pagebuilder-layout-meta.md) (`src/layout/page.php`, partials, `PageBuilder`, `MetaInfo`, dotted view names). The bullets below were the first sketch (one `main.php` under `src/views/layouts/`). HTML rules in this ADR still apply.

- Default layout: document shell + header/footer (ADR-0016), not a second HTML document in the view.
- Page views: **only** inner HTML for `<main>`.
- `Framework\Core\Controller::render(string $view, array $data = []): void` (HTTP contract: ADR-0014):
  1. Buffer the view (`ob_start` / `ob_get_clean`) → `$content`.
  2. PageBuilder requires the shell (same request). Layout sees `$content` plus MetaInfo (title, css, js). `$data` is view locals, not `<head>`.
  3. Write the finished HTML onto the Controller’s inner Response (`setBody`). Do not return a Response; glue calls `send()` after `run()`.
- Use **`require`**, not `require_once`, for views, layouts, and components. `require_once` skips a second include of the same file (two Buttons on one page would break).
- Prefer not to `extract()` blindly; if used, `EXTR_SKIP` and never pass a prop named `content` (clashes with the slot).
- **Title / meta:** `MetaInfo` (ADR-0016) into `<title>` and `<head>`. Later fields (canonical, JSON-LD) stay on that object, not a new layout file per page.
- **Theme / other layouts:** extra shells (`admin`, `auth`) later. Not required for the first layout.

File-based `IndexClass::run(): void` calls `$this->render(...)` (ADR-0014). Error views may keep a dedicated layout or the same `main` later.

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
- Implementation order: 1.1 front controller → 1.2 Request/Response + 1.3 `render` / PageBuilder (built together) → `component()` helper → static components → Table + fragment endpoint after wave 2 → Vite when TS is needed.
