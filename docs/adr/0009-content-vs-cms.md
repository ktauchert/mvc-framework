# ADR-0009: Headless page builder (Sanity pattern) as a flavor, not the HTTP kernel

- Status: Accepted
- Date: 2026-10-10

## Context

The target includes **company marketing sites**: create and fill a page quickly, without a deploy. Reference: [fahrschule-rookie](https://github.com/ktauchert/fahrschule-rookie) — Next.js + Sanity, **page builder**: the developer defines modules (Hero, Teaser, Locations), Studio offers them as choices, a page is a list `{ type, …fields }`, `ModuleRenderer` maps `type` to React components.

The same construct here (amended by ADR-0012): PHP + Postgres instead of Sanity, PHP admin views instead of Studio, public pages as file-based `IndexClass` routes instead of Next `[slug]`. Blocks are PHP partials (or later a registry), not Vue/React components.

This is **not** a kernel feature on day one. It is a **flavor/module on top of the core**. Without a prior decision, retrofitting is expensive (no JSONB, no slug pages, writes without auth, one controller per marketing page).

## Mapping Sanity → this framework

| Sanity / driving school | Here |
|---|---|
| Schema (`heroCtaModule`) | Block type: name + field list (validation in PHP) |
| Studio | PHP admin UI, auth, write routes |
| GROQ + dataset | Load page by slug from Postgres |
| `modules[]` | JSONB array `[{ "type": "hero", "headline": "…" }, …]` |
| `ModuleRenderer` | PHP map `type` → view partial |
| `app/[slug]` | File route and/or dynamic `IndexClass` (CMS pages) |

The developer writes components **and** registers the block type. Editors pick only registered types — not a free-HTML CMS.

## Alternatives

1. Code-only pages — the company customer cannot fill content.
2. Page builder in the kernel — HTTP/PDO/auth get buried.
3. Keep Sanity beside us — a second backend, contradicts “one PHP API”.
4. **Core stays headless-capable; page builder = later module** with a fixed data contract (page + blocks JSONB + registry).

## Decision

Option 4.

**Kernel / prepare now:**

- Persistence can store JSON/JSONB (Postgres can).
- Resources with `slug`; public reads vs protected writes (auth follows).
- App routes (login, admin) stay code (0005). CMS pages are *one* dynamic route plus API.
- No studio, media library, or block catalog in the first kernel.

**Module later (own ADR, after auth + first table):**

- Table `pages` (slug, title, status, `modules` JSONB, timestamps).
- Block registry (allowed `type`s + schema); API rejects unknown types.
- Admin UI: create a page, pick/fill/sort blocks.
- Public PHP `render()` + layout. Sitemap/robots: ADR-0010.
- Optional `make:block` later: PHP partial + registry (DX).

Login stays code. Not every page is a builder document.

## Consequences

- The company-site product is a flavor; create-project can offer it later.
- Retrofit = module + JSONB + one catch-all route, not a second routing model.
- Admin “block picker” lists registered PHP partials, not Vue SFCs.
