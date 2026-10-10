# ADR-0005: Scaffold — API immer, Page nur auf Wunsch

- Status: Accepted
- Datum: 2026-10-10

## Kontext

Ziel ist **Entwicklungskomfort**, nicht Laufzeit-Kopplung. Wiederkehrendes Anlegen von Controller, Route und ggf. Page soll ein CLI abnehmen; nach dem Generate sind es normale Dateien.

Nicht für jede API-Route eine Page (kein 1:1). Nur **Bildschirme** (Login, Liste, Dashboard). Reine Daten-/Aktions-Endpunkte (`GET /api/me`, `POST /api/login`, Nested) bleiben API-only, View von Hand.

Hängt an ADR-0002, 0003 und 0006 (Adapter). Welches Frontend zuerst: ADR-0004.

## Alternativen

1. Kein Generator — alles von Hand (maximaler Lernwert am Anfang).
2. Jede API-Route erzeugt eine Page — zu viele leere Hüllen, falsches 1:1.
3. Zwei Befehle bzw. ein Flag: API-Ressource immer; Page nur wenn es eine echte Bildschirm-Route ist.
4. Inertia: Page-Route *ist* PHP-Route — anderes Modell, hier nicht.

## Entscheidung

Generator (erst bauen, wenn der Kern von Hand sitzt) unterscheidet:

| Befehl (Arbeitstitel) | PHP | Frontend |
|---|---|---|
| `make:api posts` | Controller, Model, `/api/posts` | nichts |
| `make:page posts` | optional dieselben API-Teile, falls noch fehlen | TanStack: Route-Datei im Routes-Ordner (Konvention `/posts` → Fetch `/api/posts`). Andere Adapter später (0006). |

`make:page` nur für Bildschirme, nicht für `POST /api/login` oder `/api/posts/{id}/comments`. Login-Seite ist eine Page; Login-Submit bleibt API ohne zweite Page.

Keine Templates nach `public/`. Page-Dateien und Router-Eintrag schreibt der **aktuelle Frontend-Adapter** (0006), nicht ein hartes Vue-Template.

Konvention, keine Laufzeit-Bindung: Name `posts` → URL `/posts` + Fetch `/api/posts`. Weicht die Page davon ab, nach dem Generate von Hand.

## Folgen

- Weniger Dateileichen, klarer Unterschied Seite vs. Endpoint.
- Script muss wissen, welches Frontend (0004) und dass Pages ≠ HTTP-Methoden.
- Erst sinnvoll, wenn du Routing und eine Page einmal ohne Generator gebaut hast.
