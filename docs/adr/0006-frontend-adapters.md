# ADR-0006: Frontend-Adapter — Erkennung über package.json

- Status: Accepted
- Datum: 2026-10-10

## Kontext

ADR-0002 macht die API frontend-agnostisch. ADR-0005 will `make:page` nur für echte Seiten. Es sollen später **mehrere** View-Stacks möglich sein, ohne das PHP-Init (0003) an Vue zu binden.

Beispiele: TanStack Router (erster Adapter, ADR-0004), später Vue Router, React Router. Das CLI soll Routen **im jeweiligen System** einrichten können und erkennen, was im Frontend-Ordner wirklich liegt — automatisch oder mit Rückfrage.

Das ist festes Zielbild, nicht nur eine lose Idee. Umsetzung der Adapter **nach** dem ersten von Hand gebauten Frontend, nicht am Tag 1.

## Alternativen

1. Ein fest verdrahtetes Vue-Scaffold in Composer — widerspricht 0003 und blockiert React/TanStack.
2. Konfig-Datei `frontend.stack=vue` pflegen — klar, aber doppelt zu `package.json`.
3. **Adapter:** `package.json` (dependencies/devDependencies) auslesen; passenden Adapter wählen; bei Unklarheit nachfragen.

## Entscheidung

Variante 3. Scaffold-Scripts (`make:page`) sprechen einen **Frontend-Adapter**, nicht Vue direkt.

Erkennung (Reihenfolge grob): Abhängigkeiten in `frontend/package.json`, z. B.

- `@tanstack/react-router` → TanStack-Adapter (Default-Ziel nach 0004)
- `vue-router` → Vue-Adapter (später)
- `react-router` / `react-router-dom` → React-Router-Adapter (optional)

Ein Treffer: Adapter nutzen (optional kurze Bestätigung). Keiner oder mehrere: nachfragen, nicht raten. Unbekanntes Setup: abbrechen mit Hinweis, Page von Hand.

Jeder Adapter weiß, wo Page-Dateien liegen und wie eine Route in **diesem** Router registriert wird. PHP-Routen unter `/api` bleiben gleich.

Nuxt/Next sind eigene Apps; eigene Adapter nur per späterem ADR.

## Folgen

- PHP-Kern und Create bleiben stabil, während Views wachsen.
- `make:api` braucht keinen Adapter; `make:page` schon.
- Erster Adapter = TanStack Router (0004); Vue danach; kein PHP-Rewrite.
- Falsch-Positive in `package.json` → deshalb Bestätigung erlaubt.
