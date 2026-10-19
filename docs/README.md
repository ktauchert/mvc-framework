# Documentation

PHP MVC, file-based routing, PHP views (ADR-0012). Layout via PageBuilder (ADR-0016). JSON file routes under `/api/` (ADR-0017, not built yet). Core built step by step.

| Location | Purpose |
|---|---|
| [roadmap.md](roadmap.md) | Phases, done-when, remaining tasks |
| [http-flow.md](http-flow.md) | Request → render → Response (file map, not full code) |
| [assets.md](assets.md) | Dart Sass: `src/styles` → `public/css/app.css`, install + Composer scripts |
| [adr/](adr/) | *Why* (decisions) |
| [adr/0012-php-mvc-file-routing.md](adr/0012-php-mvc-file-routing.md) | Current view + routing (supersedes SPA/Vue ADRs) |
| [adr/0016-pagebuilder-layout-meta.md](adr/0016-pagebuilder-layout-meta.md) | PageBuilder, `src/layout/`, MetaInfo, dotted views |
| [adr/0017-api-file-routes.md](adr/0017-api-file-routes.md) | `/api/…`, `RouteClass`, `ApiController`, classmap |
| [adr/0018-mvc-cli.md](adr/0018-mvc-cli.md) | `php mvc`, `make:page` / `make:api` / later `make:model` |
| [../README.md](../README.md) | Project overview |
| [../CONTRIBUTING.md](../CONTRIBUTING.md) | How to work in this repo |
| [../AGENTS.md](../AGENTS.md) | Companion rules |

**Accepted (current):** 0001, 0003 (amended), 0007, 0008 (amended), 0009, 0010, 0011, **0012**, **0013**, **0014**, **0015**, **0016**, **0017**, **0018**.

**Superseded:** 0002, 0004, 0005, 0006 (SPA / JS adapters) — kept as history.

**Later waves:** PDO usage, `component()`, auth, CMS blocks as PHP partials, create-project.

Language: English (identifiers, comments, docs).
