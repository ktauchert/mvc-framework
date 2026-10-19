# MVC Framework

A **PHP-only MVC** (ADR-0012). You own the core. Views are PHP HTML, not Vue or React. Routing is **file-based**, Next.js-shaped: a folder under `src/app/` is a URL. The document shell lives in `src/layout/` and is assembled by `PageBuilder` (ADR-0016).

- **PHP ≥ 8.4**, `declare(strict_types=1);` in every PHP file (ADR-0011)
- **PostgreSQL** via PDO, Docker Compose (ADR-0007)
- **View:** PHP templates, `Controller::render()`, layout shell + partials
- **Not** Vue/Nuxt/Inertia as the page layer. JSON file routes: `/api/…` → `src/api/…/RouteClass.php` (ADR-0017; Resolver not wired yet).

## How a request runs (today)

`composer serve` → `php -S … -t public public/router.php`

1. `public/router.php` serves a real file under `public/`, or requires `index.php`.
2. `index.php` captures `Request`, resolves the page class, `run()`, `send()`.
3. `/test` → `src/app/test/IndexClass.php` → `render('test.index', …)` → `src/views/test/index.php` inside `src/layout/page.php`.
4. `/` → `src/app/IndexClass.php` (add this file when you want `/`).
5. Errors use `Framework/Views/errors/`.

```
GET /test
  → public/router.php
  → public/index.php
  → src/app/test/IndexClass.php
  → IndexClass::run() → render → PageBuilder → Response::send()
```

Scaffold (phase 1.4): `php mvc make:page notes` (ADR-0018). JSON later: `php mvc make:api notes`.

## Status

Wave 1: **1.1** and **1.2** are done. Current work is leftover **1.3** (PageBuilder / MetaInfo / page types) — see [docs/roadmap.md](docs/roadmap.md).

## Docs

| Doc | What |
|---|---|
| [docs/roadmap.md](docs/roadmap.md) | Phases |
| [docs/http-flow.md](docs/http-flow.md) | File map for a request |
| [docs/assets.md](docs/assets.md) | Dart Sass install + `composer assets` |
| [docs/adr/](docs/adr/) | Decisions (0012 supersedes SPA/Vue ADRs) |
| [CONTRIBUTING.md](CONTRIBUTING.md) | How to work |
| [AGENTS.md](AGENTS.md) | Companion rules |

## Run

```bash
composer install
cp .env.example .env
docker compose up -d
composer serve
```

Open `http://localhost:8000/test`.

CSS: install the [Dart Sass](https://github.com/sass/dart-sass/releases) binary, then `composer assets`. Details: [docs/assets.md](docs/assets.md).

## License

Not chosen yet.
