# ADR-0007: Persistenz — PostgreSQL Default, lokal Docker Compose

- Status: Accepted
- Datum: 2026-10-10

## Kontext

Ernstes Framework (0008): produktive SQL-Defaults, nicht Spielzeug-SQLite als Leitlinie. PDO bleibt die SQL-Implementierung des Persistenz-Ports. Lokal soll die DB ohne „bring your own Server“ startbar sein; Init später soll Engine und Betriebsart wählen können.

## Alternativen

1. SQLite Default — null Ops, schwächeres Produktiv-Abbild.
2. MySQL Default — üblich in PHP, hier nicht gewählt.
3. **PostgreSQL Default**, lokal **Docker Compose** (Image, User, Passwort, DB-Name über `.env`). Init später: Compose mitliefern *oder* bestehende Postgres-URL *oder* eigene MySQL-Instanz eintragen.
4. Nur „externe DB, keine Compose-Datei“ — mehr Reibung beim ersten Start.

## Entscheidung

Variante 3. **Zuerst und als Default: dockerized PostgreSQL.**

- API: **PDO** — dieselben Methoden (`prepare`, `execute`, Transaktionen, …) für alle SQL-Treiber. `new PDO($dsn, $user, $pass, $options)` gilt auch für SQLite; dort ist die DSN ein Dateipfad (`sqlite:…`), User/Passwort meist leer.
- Welcher Treiber: PHP lädt `pdo_pgsql` / `pdo_mysql` / `pdo_sqlite`; PDO wählt am **DSN-Präfix**. Neue Engine = Treiber in PHP + DSN-Zweige in Connection/Docs, nicht eine zweite DB-API.
- Reihenfolge: **jetzt nur Postgres** (`pdo_pgsql`) als Default und Entwicklungsbasis. **Danach** MySQL/MariaDB (`pdo_mysql`), dann SQLite (`pdo_sqlite`). Nicht alle drei parallel im ersten Wurf.
- SQL-Dialekt (Serial vs. Auto-Increment, Quotes) ist die eigentliche Portabilitätsarbeit; PDO-Methoden sind nicht das Problem.
- Repo: `docker-compose.yml` (Arbeitstitel) für Postgres; Credentials nicht hardcoden, sondern `.env` / `.env.example` (User, Passwort, DB, Port).
- App-Connection dieselbe URL/Vars, ob Compose oder externe Instanz.
- MySQL/MariaDB und SQLite: später gleicher Persistenz-Port, andere DSN; Init-Menü erst wenn der jeweilige Treiber unterstützt ist.
- Sessions zuerst weiter PHP-Dateien; Auth-`users` in Postgres.
- **Init (später, Create-Project):** wählen z. B. „Compose mit Postgres“, „ich habe schon Postgres“, „ich habe schon MySQL“. Composer installiert weiterhin kein npm (0003).

Mongo/Firebase/Supabase-JS: unverändert nicht der Kern. Supabase-Postgres wäre Fall „bestehende Postgres-URL“.

## Folgen

- Erstes ernstes Setup braucht Docker (oder eine erreichbare Postgres).
- `pdo_pgsql` auf dem PHP-Host bzw. im PHP-Container.
- Compose ist Dev-Default, nicht automatisch Produktion (managed Postgres dort üblich).
- Secrets nur in `.env`, nie committen.
- Jede weitere SQL-Engine: ADR oder Docs-Abschnitt (Extension-Name, DSN, Dialekt-Fallen) und Connection um den Treiber erweitern.
