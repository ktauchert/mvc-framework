# MVC Framework

A **PHP-only MVC** (ADR-0012). You own the core. Views are PHP HTML, not Vue or React. Routing is **file-based**, Next.js-shaped: a folder under `src/app/` is a URL.

- **PHP ≥ 8.4**, `declare(strict_types=1);` in every PHP file (ADR-0011)
- **PostgreSQL** via PDO, Docker Compose (ADR-0007)
- **View:** PHP templates, layouts, and `render()` (base `Controller` still to add)
- **Not** Vue/Nuxt/Inertia as the page layer. JSON may live under `src/api/` later.

## How a request runs (today)

`composer serve` → `php -S … -t public public/router.php`

1. `public/router.php` takes the URL path.
2. `/test` → file `src/app/test/IndexClass.php`, class `App\Controllers\Test\IndexClass`.
3. `/` → `src/app/IndexClass.php` (home; add this file when you want `/`).
4. `run()` executes (today: `echo` HTML). Errors use `Framework/Views/errors/`.

```
GET /test
  → public/router.php
  → src/app/test/IndexClass.php
  → IndexClass::run()
```

Scaffold: `composer make-route test`.

## Status

ADR-0012 is the view/routing decision. Kernel exists in sketch form (dispatcher, config, dotenv, error views, one `/test` route). Next: base `Controller`, `render()`, layouts — see [docs/roadmap.md](docs/roadmap.md).

## Docs

| Doc | What |
|---|---|
| [docs/roadmap.md](docs/roadmap.md) | Phases |
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

## License

Not chosen yet.
