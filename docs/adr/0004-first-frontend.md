# ADR-0004: Welches Frontend zuerst?

- Status: Accepted
- Datum: 2026-10-10

## Kontext

PHP ist entschieden (API, JSON, getrenntes Init). Hier nur die **erste** View: von Hand lernen, danach erster `make:page`-Adapter (0006). Weitere Stacks (inkl. Vue) bleiben erlaubt.

React ist bekanntes Wasser; Vue soll später trotzdem mal drankommen — als zweiter Adapter, nicht als Start.

## Was diese Entscheidung nicht ist

- Nicht PHP-Architektur (0002).
- Nicht „nur React für immer“ (0006).
- Nicht Composer installiert das Frontend (0003).

## Alternativen

1. Vue 3 + Vite + Vue-Router
2. React + Vite + React-Router
3. Nuxt oder Next zuerst (zu schwer, nicht Start)
4. **React + Vite + TanStack Router** (file-based Routes: neue Seite = Datei im Routes-Ordner)

## Entscheidung

**Variante 4.** Erstes Frontend: React mit Vite und **TanStack Router**, als **SPA** gegen die PHP-API. TanStack Start (SSR) bewusst nicht der erste Wurf; Router *kann* später SSR, siehe ADR-0010.

`make:page` schreibt vor allem eine Datei in den Routes-Ordner; der Router pickt sie über Konvention auf. Vue-Bekanntschaft: später eigener Adapter.

## Folgen

- Erster 0006-Adapter = TanStack Router (React).
- `make:page posts` ≈ Route-Datei anlegen, kein zentrales `routes.ts` patchen.
- PHP `/api` unverändert.
- Vue bleibt Lernziel Nr. 2, nicht blockiert.
