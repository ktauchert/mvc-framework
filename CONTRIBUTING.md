# Contributing

This is a **serious, self-built** PHP MVC (ADR-0008, ADR-0012). Prefer small phases over large dumps. Chat may be German; **everything in the repo is English** (code, comments, docs, commits).

## Before you write code

1. Read the [roadmap](docs/roadmap.md) phase you are on.
2. Check related [ADRs](docs/adr/) — **0012** is the current view/routing model.
3. Name the phase in the PR or when asking the companion (`AGENTS.md`).

Do not add Vue/React as the page renderer. Do not skip to CMS or auth before that wave.

## Language and PHP

- PHP ≥ 8.4, `declare(strict_types=1);` at the top of every PHP file
- PSR-4: `Framework\` → `Framework/`, `App\` → `src/app/`, `Api\` → `src/api/`
- File route: `src/app/test/IndexClass.php` → `GET /test` → `run()`
- Typed properties, parameters, returns; constructor promotion / `readonly` where values do not change
- PDO **prepared** statements only
- No secrets in Git (`.env` stays local)

## Git

- Do not commit: `vendor/`, `node_modules/`, `.env`, IDE junk, `.obsidian/`
- Prefer focused commits (“why”, not a file dump)
- `gh` + `gh auth setup-git` for HTTPS push (GitHub rejects account passwords)

## Reviews

Ask for a **hint** or a **review**, and include the phase id plus what you tried. The companion does not generate the kernel in one shot.

## New architecture

If it changes a cut (auth model, extra DB, bringing a JS view back): **Proposed ADR** first, then code.
