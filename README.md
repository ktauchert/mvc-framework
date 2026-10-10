# MVC Framework

A PHP JSON API with a swappable SPA view. You build the **core yourself**; this repo holds architecture, the roadmap, and (later) the skeleton.

- **PHP ≥ 8.4**, `declare(strict_types=1);` in every PHP file
- **PostgreSQL** via PDO (`pdo_pgsql`), Docker Compose locally
- **View:** React + Vite + TanStack Router (SPA); Vue later via adapters
- **Not** Laravel, Inertia, or a CMS on day one — those are documented as later flavors

## Status

Architecture is **accepted** (ADR-0001–0011). Implementation follows [docs/roadmap.md](docs/roadmap.md). There is no runnable kernel yet. Next work: **phase 0.1**.

## Docs

| Doc | What |
|---|---|
| [docs/roadmap.md](docs/roadmap.md) | Phases and learning focus |
| [docs/adr/](docs/adr/) | Architecture decisions |
| [CONTRIBUTING.md](CONTRIBUTING.md) | How to contribute / work phases |
| [AGENTS.md](AGENTS.md) | Rules for the coding agent |

## Later install (not yet)

When the skeleton exists:

```bash
composer install
cp .env.example .env
docker compose up -d
composer serve
```

Frontend (own init, not Composer):

```bash
cd frontend && npm install && npm run dev
```

## License

Not chosen yet. Add a `LICENSE` file when you publish the package.
