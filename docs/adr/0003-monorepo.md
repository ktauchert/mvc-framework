# ADR-0003: Ein Repository, getrennte Inits (Composer und npm)

- Status: Accepted
- Datum: 2026-10-10

## Kontext

Ein Clone soll das Grundgerüst enthalten (wie nach `laravel new` / `symfony new` / `create-next-app`), aber hier sind zwei Ökosysteme im Spiel. Composer soll **nicht** implizit Vue/React anlegen — sonst klebt das PHP-Init am ersten Frontend und verbirgt den Schnitt aus ADR-0002.

## Alternativen

1. Zwei Repos — früher Overhead, widerspricht „ein Pull“.
2. Ein Repo, **ein** Super-Init das Composer und `npm create vue` hintereinander ausführt — bequem, koppelt das Framework an ein UI-Toolkit.
3. Ein Repo, **zwei** Inits: PHP-Skeleton via Composer (oder eigenes `create`-Kommando); Frontend via npm/Vite im Frontend-Ordner, getrennt und bewusst.

## Entscheidung

Variante 3. Ein Repository. PHP-Init und Node-Init bleiben getrennt.

- PHP: `composer install` / später `create-project` (oder CLI `new`) erzeugt nur den PHP-Kern.
- Frontend: `npm create` / `npm install` im Frontend-Ordner, wenn die View an der Reihe ist.
- Composer führt kein `npm` und kein Vue/React-Scaffold aus.
- Lokal zwei Dev-Prozesse; Produktion: PHP-Document-Root plus gebautes Bundle in `public/`.

Ordnernamen konkretisieren, wenn die erste Schicht gebaut wird. Frontend-Erkennung und Page-Scaffold: ADR-0006.

## Folgen

Ein Clone reicht fürs Repo; zum Laufen braucht es zwei Installer. Erstes Frontend (0004) ist unabhängig vom PHP-Create. Später weitere Frontends ohne PHP-Init umzubauen.
