# ADR-0007: Persistence — PostgreSQL default, Docker Compose locally

- Status: Accepted
- Date: 2026-10-10

## Context

Serious framework (0008): production-like SQL defaults, not toy SQLite as the guideline. PDO remains the SQL implementation of the persistence port. Locally the DB should start without “bring your own server”; later init should be able to choose engine and how it is run.

## Alternatives

1. SQLite default — zero ops, weaker production picture.
2. MySQL default — common in PHP, not chosen here.
3. **PostgreSQL default**, local **Docker Compose** (image, user, password, database name via `.env`). Later init: ship Compose *or* an existing Postgres URL *or* point at your own MySQL.
4. External DB only, no Compose file — more friction on first start.

## Decision

Option 3. **First and default: dockerized PostgreSQL.**

- API: **PDO** — the same methods (`prepare`, `execute`, transactions, …) for every SQL driver. `new PDO($dsn, $user, $pass, $options)` also applies to SQLite; there the DSN is a file path (`sqlite:…`), user/password usually empty.
- Which driver: PHP loads `pdo_pgsql` / `pdo_mysql` / `pdo_sqlite`; PDO selects by **DSN prefix**. A new engine = PHP extension + DSN branches in Connection/docs, not a second DB API.
- Order: **Postgres only for now** (`pdo_pgsql`) as default and development base. **Then** MySQL/MariaDB (`pdo_mysql`), then SQLite (`pdo_sqlite`). Not all three in the first pass.
- SQL dialect (serial vs auto-increment, quoting) is the real portability work; PDO methods are not the problem.
- Repo: `docker-compose.yml` (working title) for Postgres; do not hard-code credentials — `.env` / `.env.example` (user, password, database, port).
- App connection uses the same URL/vars whether Compose or an external instance.
- MySQL/MariaDB and SQLite: later the same persistence port, different DSN; init menu only once that driver is supported.
- Sessions stay PHP files first; auth `users` in Postgres.
- **Init (later, create-project):** choose e.g. “Compose with Postgres”, “I already have Postgres”, “I already have MySQL”. Composer still does not install npm (0003).

Mongo/Firebase/Supabase JS: still not the core. Supabase Postgres would be the “existing Postgres URL” case.

## Consequences

- First serious setup needs Docker (or reachable Postgres).
- `pdo_pgsql` on the PHP host or in the PHP container.
- Compose is the dev default, not automatically production (managed Postgres is common there).
- Secrets only in `.env`, never committed.
- Each further SQL engine: ADR or docs section (extension name, DSN, dialect traps) and extend Connection.
