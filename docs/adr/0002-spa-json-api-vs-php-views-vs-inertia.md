# ADR-0002: View-Kopplung — SPA+JSON-API vs. PHP-Views vs. Inertia

- Status: Accepted
- Datum: 2026-10-10

## Kontext

Lernziel: PHP tiefer (HTTP, Routing, Controller, Models) und eine JS-View anbinden. Klassisches PHP-MVC rendert HTML. Laravel-Starter-Kits (2026) koppeln React/Vue über Inertia (PHP wählt Seite + Props). Eine SPA spricht die PHP-Seite nur per JSON an; PHP hat dann kein `Views/`-HTML mehr.

## Alternativen

1. **PHP-Templates** — ein Router, MVC in einer Sprache. Kaum Vue/React-Lernwert.
2. **Inertia-ähnlich** — PHP bleibt Seiten-Router, JS nur Darstellung. Weniger REST, eigenes Protokoll zum Nachbauen.
3. **SPA + JSON-API** — PHP: `/api` + JSON. Frontend-Router: `/login`, `/app`, …. Zwei Router, API frontend-agnostisch, PHP faktisch M+C.

## Entscheidung

**Variante 3: SPA + JSON-API.** Request/Response und REST bleiben sichtbar. Inertia später nur per eigenem ADR, nicht als verstecktes Ziel.

Gesamtsystem weiter MVC (V im Frontend). PHP-intern: API mit Models und Controllern, keine HTML-Views als Hauptweg.

## Folgen

- Seiten-URLs und API-URLs sind verschiedene Verträge, kein 1:1.
- Auth muss auf der API sitzen; Frontend-Guards sind nur UX.
- Vue und React sind austauschbar (siehe ADR-0004).
- SPA-Fallback: Nicht-API-Pfade liefern `index.html`.
