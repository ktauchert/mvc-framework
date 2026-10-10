# ADR-0008: Ernstes Framework, selbst gebauter Kern

- Status: Accepted
- Datum: 2026-10-10

## Kontext

„Lernprojekt“ hieß: du baust den Kern selbst, kein fertiger Dump. Das Produktziel ist trotzdem ein **nutzbares, sicheres Framework** für PHP-API plus SPA-Flavors (React/TanStack zuerst, später z. B. Vue) — intuitiv für Entwickler, sicher und flüssig für Endnutzer.

## Alternativen

1. Tutorial-Toy — Patterns zeigen, Sicherheit und DX nachrangig.
2. Framework-Klon von Laravel in einem Rutsch — du besitzt den Kern nicht.
3. **Ernstes Design, schrittweise Implementierung** — ADRs und Sicherheit von Anfang an; Create-Project später als Verpackung des gebauten Kerns.

## Entscheidung

Variante 3. Qualitätsmaß: Prepared Statements, bewusste Auth/Session, keine Geheimnisse im Repo, vorhersehbare CLI (0005/0006), SPA die sich schnell anfühlt (kein Extra-Roundtrip ohne Grund).

„Flavor“ = austauschbare View (0004/0006) plus später austauschbare Persistenz-Adapter (0007), nicht fünf halbe Kerne.

## Folgen

- SQLite zum Start ist ein *Engine-Default*, kein Spielzeug-Anspruch.
- Features dürfen warten; unsichere Abkürzungen nicht „weil Lernprojekt“.
