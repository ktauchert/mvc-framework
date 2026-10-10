# ADR-0001: Docs und ADRs statt Architektur in AGENTS.md

- Status: Accepted
- Datum: 2026-10-10

## Kontext

`AGENTS.md` wurde als Sammelstelle für Zielbild, Ordnerbaum, Laravel-Vergleich und Routing genutzt. Die Datei ist für Agent-Verhalten gedacht. Architektur soll diskutiert und versioniert werden, nicht stillschweigend in Prompt-Text wachsen.

## Alternativen

1. Alles in `AGENTS.md` halten — kurzfristig bequem, vermischt Rollen, keine Entscheidungshistorie.
2. Ein großes `architecture.md` ohne ADRs — lesbar, aber „warum nicht Inertia?“ verschwindet.
3. `docs/` plus einzelne ADRs.

## Entscheidung

- `AGENTS.md`: nur Coach-Regeln, Verweis auf `docs/`.
- `docs/adr/`: eine Entscheidung pro Datei.
- Kein zweites Architektur-Wiki, das ADRs doppel.

## Folgen

Neue Wege zuerst als Proposed-ADR, dann akzeptieren oder verwerfen. Chat bleibt Erörterung; die Entscheidung landet im ADR.
