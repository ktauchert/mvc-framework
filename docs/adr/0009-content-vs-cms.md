# ADR-0009: Headless Page-Builder (Sanity-Muster) als Flavor, nicht als HTTP-Kern

- Status: Accepted
- Datum: 2026-10-10

## Kontext

Zielbild sind u. a. **Firmenkunden-Websites**: schnell eine Seite anlegen und befüllen, ohne Deploy. Referenz: [fahrschule-rookie](https://github.com/ktauchert/fahrschule-rookie) — Next.js + Sanity, **Page Builder**: Entwickler definiert Module (Hero, Teaser, Locations), Studio bietet sie zur Auswahl, die Page ist eine Liste `{ type, …felder }`, `ModuleRenderer` mappt `type` auf React-Komponenten.

Dasselbe Konstrukt hier: PHP-API + Postgres statt Sanity, Admin-SPA statt Studio, öffentliche TanStack-SPA statt Next `[slug]`. React-Blöcke zuerst (0004); Vue-Blöcke später als Frontend-Adapter (0006).

Das ist **kein** Kernel-Feature am Tag 1. Es ist ein **Flavor/Modul auf dem Kern**. Ohne Vorentscheidung wird Nachrüsten teuer (kein JSONB, keine Slug-Pages, Writes ohne Auth, feste Controller pro Marketing-Seite).

## Abbildung Sanity → dieses Framework

| Sanity / Fahrschule | Hier |
|---|---|
| Schema (`heroCtaModule`) | Block-Typ: Name + Feldliste (Validierung in PHP) |
| Studio | Admin-SPA, Auth, `POST/PATCH /api/pages` |
| GROQ + Dataset | `GET /api/pages/{slug}` aus Postgres |
| `modules[]` | JSONB-Array `[{ "type": "hero", "headline": "…" }, …]` |
| `ModuleRenderer` | React-Map `type` → Komponente |
| `app/[slug]` | TanStack-Route `/$slug` (CMS-Pages), nicht jede App-Route |

Entwickler schreibt Komponenten **und** registriert den Block-Typ. Redakteur wählt nur registrierte Typen — kein freies HTML-CMS.

## Alternativen

1. Nur Code-Pages — Firmenkunde kann nicht befüllen.
2. Page-Builder in den Kernel — HTTP/PDO/Auth gehen unter.
3. Weiter Sanity daneben — zweites Backend, widerspricht „eine PHP-API“.
4. **Kern headless-fähig; Page-Builder = späteres Modul** mit festem Datenvertrag (Seite + Blöcke JSONB + Registry).

## Entscheidung

Variante 4.

**Kernel / jetzt nur vorbereiten:**

- Persistenz kann JSON/JSONB (Postgres kann das).
- Ressourcen mit `slug`; öffentliche Reads vs. geschützte Writes (Auth folgt).
- App-Routen (Login, Admin) bleiben Code (0005). CMS-Seiten sind *eine* dynamische Route plus API.
- Kein Studio, keine Medienbibliothek, kein Block-Katalog im ersten Kern.

**Modul später (eigener ADR, nach Auth + erster Tabelle):**

- Tabelle `pages` (slug, title, status, `modules` JSONB, timestamps).
- Block-Registry (erlaubte `type`s + Schema); API lehnt unbekannte Typen ab.
- Admin-UI: Seite anlegen, Blöcke wählen/befüllen/sortieren.
- Öffentliche SPA: Renderer wie in der Fahrschule. Sitemap/Robots: ADR-0010.
- Optional `make:block hero` erzeugt React-Komponente + Registry-Eintrag (DX, 0005/0006).

Login bleibt Code. Nicht jede Page ist ein Builder-Dokument.

## Folgen

- Firmenkunden-Produkt ist ein Flavor, Create-Project kann es später anbieten.
- Nachrüsten = Modul + JSONB + eine Catch-all-Route, kein zweites Routing-Modell.
- Vue-Komponenten im Admin „zur Wahl“ = zweiter Frontend-Adapter, gleicher JSON-Vertrag.
