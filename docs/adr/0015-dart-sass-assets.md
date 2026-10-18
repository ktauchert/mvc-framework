# ADR-0015: Dart Sass for CSS, Composer as the only Dev command

- Status: Accepted
- Date: 2026-10-14
- Depends on: ADR-0003 (Composer does not scaffold npm/Vue), ADR-0012 (PHP views)
- Amends: ADR-0003 “optional Tailwind CLI later” — CSS is Dart Sass first; Tailwind would need a new ADR
- Later note (2026-10-16): page SCSS files and `@use` from `make-route` — see Decision, “Page styles”

## Context

PHP layouts need CSS. The Dev should not run Composer **and** npm for everyday work. Sources must not live in `public/` (Apache/`php -S` should only see built files).

## Alternatives

1. Hand-written CSS only in `public/css/` — no compiler, weak theming/partials.
2. Tailwind CLI — utilities in PHP views; extra tool; already optional in older notes.
3. npm `sass` / Vite — second ecosystem the Dev must install and remember.
4. **Dart Sass binary** + Composer scripts — one human entry (`composer assets`); no `package.json` for CSS.

## Decision

Option 4.

- Author SCSS under `src/styles/`. One entry: `src/styles/app.scss` (`@use` thematic partials).
- Compile to **one** file: `public/css/app.css`. The layout links that file only (no glob of `public/css/*.css`).
- Install the compiler from [Dart Sass releases](https://github.com/sass/dart-sass/releases) (put `sass` on `PATH`). Not via Composer, not via npm.
- Dev commands: `composer assets` (once), `composer assets:watch` (while editing SCSS). `composer install` does **not** run Sass (ADR-0003: PHP init stays PHP).
- `composer serve` stays the PHP server only. Watch Sass in a second terminal, or a later `composer dev` wrapper — not required to document the tool.

### Page styles (`make-route`, phase 1.4)

Chrome lives under `src/styles/main/` (header, footer, document). **Per-page** CSS lives next to that idea, not mixed into `main/`:

| Route | View | SCSS |
|---|---|---|
| `/notes` | `src/views/notes/index.php` | `src/styles/pages/notes/index.scss` |
| `/` | `src/views/index.php` | `src/styles/pages/index.scss` |

`src/styles/app.scss` remains the **only** Sass entry. `composer make-route notes` (HTML, not `--api`) should:

1. Create the page SCSS file if missing (stub comment is enough; the Dev fills rules).
2. Add `@use 'pages/notes/index';` to `app.scss` if that line is not already there.

`@use` must sit with the other `@use` lines (Dart Sass: `@use` before any CSS rules). Do not append after later CSS in `app.scss`. Do not overwrite an existing SCSS file. `--api` creates **no** SCSS. Skip with `--no-scss` if a page has no stylesheet.

`make-route` does **not** run `composer assets`. The Dev fills the SCSS; watch or `composer assets` compiles into `/css/app.css`. Still one `<link>` (ADR-0016).

## Consequences

- Clone + `composer install` still runs PHP without Sass. Changing SCSS requires the Dart Sass binary and `composer assets`.
- No npm for this pipeline. A SPA or Tailwind would be a new ADR, not a silent second init.
