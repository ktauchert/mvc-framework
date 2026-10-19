# ADR-0018: App CLI `php mvc` — `make:page`, `make:api`, later `make:model`

- Status: Accepted
- Date: 2026-10-19
- Depends on: ADR-0012 (file routes, PHP views), ADR-0015 (page SCSS), ADR-0017 (JSON file routes)
- Amends: ADR-0012 (`composer make-route` / `scripts/make-route.php`); ADR-0015 (`make-route` for page styles); ADR-0017 (`make-route --api`); ADR-0008 (CLI name)

## Context

Pages are scaffolded today by `composer make-route` → `scripts/make-route.php`. That script predates `Controller`, views, and page SCSS. Composer scripts also swallow flags: `composer make-route notes --no-scss` is not passed through unless the caller inserts `--`.

Laravel uses `php artisan make:controller`; Symfony uses `php bin/console make:controller`. Generators with options live on a **PHP CLI**, not on Composer. Composer stays install, autoload, and thin aliases (`test`, `serve`, `assets`).

ADR-0005 already used the names `make:page` / `make:api`, but for a SPA adapter (superseded by ADR-0012). The names are still the right split: a **screen** vs a **JSON endpoint**, not one command plus `--api`.

## Alternatives

1. Keep `composer make-route` and document `composer make-route notes -- --no-scss` — one human entry, awkward flags, one command for two kinds of file.
2. `php bin/mvc` — Symfony-shaped; `bin/` matters mainly when Composer publishes a package binary to `vendor/bin`.
3. One `make-route` command with `--api` — fewer words, weaker signal, Composer-`--` forever.
4. **Root `mvc` (like `artisan`) and separate `make:*` commands.**

## Decision

Option 4.

### Entry

- One file at the **project root**: `mvc` (no `bin/` directory required).
- Humans run `php mvc …`. Optional shebang so `./mvc` works; not required for the contract.
- No arguments (or `help`): English usage, list of commands, exit 0 (help) or 1 (unknown command).
- **One process.** The entry parses `$argv` and runs the generator in-process. Do not `passthru` a second PHP script per command.
- Do not add Symfony Console or a Laravel-style kernel in wave 1. A `switch` / small command map is enough until a later ADR.

Composer does **not** become the generator. Optional alias only, e.g. `"make-page": "php mvc make:page"`. `composer assets`, `composer serve`, and `composer test` stay Composer scripts (ADR-0003, ADR-0015).

### Commands

| Command | When | Writes |
|---|---|---|
| `make:page {path}` | Phase **1.4** | `src/app/…/IndexClass.php`, matching view, default page SCSS + `@use` in `app.scss` (ADR-0015) |
| `make:api {path}` | Phase **1.5** | `src/api/…/RouteClass.php` only (ADR-0017) |
| `make:model {path}` | Wave **2** (not specified here) | a model class later; no pages, no API routes |

`{path}` is the resource path (`notes`, `auth/login`, `/` for home). For `make:api` there is **no** leading `api/` in the argument; the command adds the `/api` URL prefix and the `src/api/` tree.

Until a command exists, `php mvc make:api` / `make:model` must **refuse** in English (exit 1), not silently write files.

### `make:page` (phase 1.4)

```bash
php mvc make:page notes
php mvc make:page notes --no-scss
php mvc make:page notes --force
php mvc make:page /
```

- Home `/` → `src/app/IndexClass.php` (ADR-0012). Missing path argument is an error, not home.
- `--no-scss` skips the page SCSS file and the `app.scss` `@use` (ADR-0015).
- `--force` overwrites existing **IndexClass and view**. Existing SCSS is never overwritten, even with `--force`.
- Refuse overwrite of class/view without `--force` (exit 1, write nothing).
- Harden the path (`..`, absolute paths, empty segments). English stdout/stderr.
- After a successful write: remind `composer dump-autoload` (classmap). Do **not** run `composer assets`.
- No `--api`. That kind of file is `make:api`.

Stubs: load template files, do not concatenate PHP strings in the command. `declare(strict_types=1);`, `extends Controller`, `run(): void`, Meta on `$this->metaInfo`, `render('…', [])`. Inner view is `<main>` HTML only. `mkdir` must not use mode `0777`.

### `make:api` (phase 1.5, not 1.4)

```bash
php mvc make:api notes
```

Writes `RouteClass` under `src/api/`. No view, no SCSS, no `--no-scss`. `--force` same idea as pages (class file only). Does not patch `index.php` or the Resolver; wiring is ADR-0017.

### What this is not

- Not `vendor/bin` packaging (later `create-project` can still ship root `mvc`).
- Not auth (`auth/login` is only a path).
- Not `component()` scaffolding (ADR-0013, later).

## Consequences

- Flags work without Composer `--`. Command name tells HTML vs JSON vs model.
- `scripts/make-route.php` is replaced by `mvc` plus in-process generators (templates may live under `scripts/templates/` or similar).
- Docs and Composer aliases that still say `make-route` follow this ADR.
- A fuller console (many commands, Symfony Console) would need a new ADR, not a silent dump.
