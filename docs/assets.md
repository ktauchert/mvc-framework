# CSS assets (Dart Sass)

PHP views stay PHP (ADR-0012). Styles are **SCSS in the app**, compiled to **one CSS file** the browser can load (ADR-0015). Composer is the only command you type; Sass is the compiler on your machine.

## Layout on disk

| Path | Role |
|---|---|
| `src/styles/` | Source. Thematic partials (`_theme.scss`, `_layout.scss`, …) |
| `src/styles/app.scss` | Bootstrap: `@use` those partials in order |
| `public/css/app.css` | Build output. Link this from the layout: `/css/app.css` |

Do not glob every file in `public/css/` from PHP. Partials are pulled in through `app.scss` only.

Do not add a second hand-written sheet next to `app.css` unless MetaInfo points at it.

## Install Dart Sass

Composer does not install the compiler. Download a release for your OS/CPU:

**[github.com/sass/dart-sass/releases](https://github.com/sass/dart-sass/releases)**

Unpack so the `sass` binary is on your `PATH`. Check:

```bash
sass --version
```

No `package.json` and no `npm` for this step.

## Commands (Composer is the source of truth)

From the repo root, after `sass` is on `PATH`:

```bash
composer assets         # src/styles/app.scss → public/css/app.css
composer assets:watch   # same, rebuild on change
composer serve          # PHP only; does not compile CSS
```

`composer install` does not run Sass. Production: run `composer assets` in the deploy pipeline (or ship a built `public/css/app.css`).

## Authoring

Add a partial, e.g. `src/styles/_buttons.scss`, then in `app.scss`:

```scss
@use 'theme';
@use 'buttons';
```

Load order in `app.scss` is the cascade. `src/layout/page.php` links `/css/app.css` via MetaInfo (ADR-0016). Partials under `src/styles/main/` follow the layout partials (`_header`, `_footer`, page).

## Page SCSS (with `make-route`, phase 1.4)

Do not hand-edit a second file under `public/css/` for a new route. When 1.4 lands:

```bash
composer make-route notes
# src/app/notes/IndexClass.php
# src/views/notes/index.php
# src/styles/pages/notes/index.scss
# @use 'pages/notes/index';  added in src/styles/app.scss
```

Then fill `src/styles/pages/notes/index.scss` and let `composer assets` / `assets:watch` rebuild. `--no-scss` skips the stylesheet. `--api` never writes SCSS.

Until that scaffold exists, add the file and the `@use` by hand using the same paths (ADR-0015).
