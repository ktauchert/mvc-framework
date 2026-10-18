# Contributing

This is a **serious, self-built** PHP MVC (ADR-0008, ADR-0012). Prefer small phases over large dumps. Chat may be German; **everything in the repo is English** (code, comments, docs, commits).

The companion (`AGENTS.md`) stays with you: tasks and *why* first. After **Go** on a work package: the companion writes the **tests** (red); you make the kernel green. Kernel dumps still only on an explicit Go for code.

## Before you write code

1. Read the [roadmap](docs/roadmap.md) phase you are on.
2. Check related [ADRs](docs/adr/) — **0012** (views/routing), **0014** (`run`/`send`), **0016** (PageBuilder / layout), **0017** (JSON `/api/` file routes).
3. Name the phase in the PR or in chat when it helps.

Do not add Vue/React as the page renderer. Do not skip to CMS or auth before that wave.

CSS: Dart Sass from [GitHub releases](https://github.com/sass/dart-sass/releases), then `composer assets` — not npm ([docs/assets.md](docs/assets.md), ADR-0015).

## Language and PHP

- PHP ≥ 8.4, `declare(strict_types=1);` at the top of every PHP file
- PSR-4: `Framework\` → `Framework/`. **Classmap:** `src/app/` and `src/api/` (lowercase folders; namespace segments `ucfirst`). Composer still lists `Api\` as PSR-4 until ADR-0017 is implemented.
- File route (HTML): `src/app/test/IndexClass.php` → `GET /test` → `run()`
- File route (JSON, ADR-0017): `src/api/notes/RouteClass.php` → `GET /api/notes` → `run()` — Resolver not wired yet
- Typed properties, parameters, returns; constructor promotion / `readonly` where values do not change
- PDO **prepared** statements only
- No secrets in Git (`.env` stays local)

## Tests

Wave 1 adds a **smoke** (PHPUnit, `composer test`): autoload, `config()` paths, `/test` via `run()` and `response()` — not `send()`, not a live `php -S`. Build `Request` in the test; do not use `Request::capture()` on the CLI. Broader suite is phase 3.2.

**Go** on a package → companion adds tests for that cut; you implement until `composer test` is green.

## Git

- Do not commit: `vendor/`, `node_modules/`, `.env`, IDE junk, `.obsidian/`
- Prefer focused commits (“why”, not a file dump)
- `gh` + `gh auth setup-git` for HTTPS push (GitHub rejects account passwords)

## Reviews

Ask for a task breakdown, a **review**, or **Go** on a named work package (tests first) or on a named kernel step. Include what you tried when you want a review. The companion does not generate the kernel in one shot.

## New architecture

If it changes a cut (auth model, extra DB, bringing a JS view back): **Proposed ADR** first, then code.
