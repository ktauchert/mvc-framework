# Umsetzungsplan

Du baust den Kern **phaseweise selbst**. Der Agent ist Companion (Review, Erklärung, nächster Happen) — siehe `AGENTS.md`. Architektur: `docs/adr/`.

**So arbeitest du eine Phase:** Nummer nennen → kurz lesen (PHP-Fokus + Quellen) → selbst bauen → Companion zum Review holen → erst dann die nächste. Generator und CMS nicht vorziehen.

**Immer an:** `declare(strict_types=1);`, Namespaces, typisierte Properties/Parameter/Returns, PDO nur mit `prepare`. Englische Bezeichner.

Ziel-PHP: **≥ 8.4** (ADR-0011).

```
public/                 Front Controller (einziges Web-Root)
src/Framework/          Kernel (Http, Routing, Database, …)
app/                    Deine App (Controllers, Models)
config/  routes/  storage/
frontend/               ab Welle 4 (React + TanStack)
```

---

## Welle 0 — Werkzeugkasten

### Phase 0.1 — Umgebung

**Ziel:** PHP 8.4+, Composer, Git, Docker. Postgres-Container startet; PHP darf zuerst **auf dem Host** laufen (`php -S`), wenn `pdo_pgsql` da ist.

**PHP-Fokus:** Version prüfen (`php -v`, `php -m` enthält `pdo_pgsql`). Noch kein OOP-Zwang.

**Du:** Git init falls nötig. `.gitignore` (`/vendor`, `.env`, `node_modules`, IDE). `.env.example` mit Postgres-Platzhaltern. `docker-compose.yml` nur Postgres, Credentials aus `.env`. `composer.json` mit PSR-4 `Framework\\` → `src/Framework/`, `App\\` → `app/`, Script `serve` → `php -S localhost:8000 -t public public/router.php` (Router-Datei in 1.1).

**Fertig wenn:** `docker compose up -d` und `psql`/Client verbindet; `composer dump-autoload` läuft; keine Secrets im Git.

**Quellen:** [php.net/install](https://www.php.net/manual/en/install.php) · [getcomposer.org](https://getcomposer.org) · [PDO PostgreSQL](https://www.php.net/manual/en/ref.pdo-pgsql.php)

**Nicht:** Frontend, Laravel installieren, PHP-FPM-Cluster.

### Phase 0.2 — OOP-Auffrischung (Lesen, wenig Code)

**Ziel:** Die Sprache, die du ab 1.1 brauchst, einmal gesehen haben.

**PHP-Fokus:** Klasse, Objekt, `public`/`private`/`protected`, Constructor Property Promotion, `readonly`, Namespaces + `use`, `declare(strict_types=1)` (sonst werden `int`-Hints koerziv). Optional: `enum`, `match`.

**Fertig wenn:** Du kannst erklären, was Promotion vs. klassische Properties ist, und warum `strict_types` in *jeder* Datei stehen muss (gilt nur dateiweise).

**Quellen:** [Classes](https://www.php.net/manual/en/language.oop5.php) · [Namespaces](https://www.php.net/manual/en/language.namespaces.php) · [strict_types](https://www.php.net/manual/en/language.types.declarations.php#language.types.declarations.strict) · [Constructor promotion](https://www.php.net/manual/en/language.oop5.decon.php#language.oop5.decon.constructor.promotion) · [Readonly](https://www.php.net/manual/en/language.oop5.properties.php#language.oop5.properties.readonly-properties)

---

## Welle 1 — HTTP-Kern (ohne DB)

### Phase 1.1 — Front Controller + Autoload

**Ziel:** Ein HTTP-Einstieg. Keine Logik in zehn PHP-Dateien unter der URL.

**PHP-Fokus:** `public/index.php` ohne Namespace (Einstieg). Rest unter `Framework\`. Autoload vs. `require`.

**Du:** `public/index.php` lädt `vendor/autoload.php`, startet `Framework\Application`. `public/router.php` für den Built-in-Server: existierende Datei ausliefern, sonst `index.php`. Application darf zuerst nur `echo` JSON `{"ok":true}` — bewusst dünn.

**Fertig wenn:** `composer serve` (oder `php -S …`) → `GET /` liefert JSON, Status 200.

**Quellen:** Front Controller (Konzept) · Composer PSR-4

**Nicht:** Router-Vollausbau in derselben Session wie „Hello JSON“, wenn du überlädst — lieber 1.2.

### Phase 1.2 — Request und Response

**Ziel:** `$_GET`/`$_SERVER`/`php://input` nicht quer durch den Code. Antwort = Objekt, das `send()` macht.

**PHP-Fokus:** `readonly` Properties, `self`/`static` Factory `Request::capture()`, Return-Typen `array`, `string`, `int`, `never` bei `send()` (terminiert). JSON: `json_encode` + Header `Content-Type: application/json`.

**Du:** `Framework\Http\Request` (`method()`, `path()`, `query()`, `json()` aus `php://input`), `Framework\Http\Response` mit `json()`, `status()`, `header()`. Application: Request bauen, Response senden. `json()` darf bei leerem Body `[]`/`null` liefern, bei ungültigem JSON lieber Exception als stilles `[]`.

**Fertig wenn:** Du Status 201 und einen JSON-Body setzen kannst, ohne `echo` in der Application.

**Quellen:** [Superglobals](https://www.php.net/manual/en/language.variables.superglobals.php) · [json_encode](https://www.php.net/json-encode)

### Phase 1.3 — Router

**Ziel:** Methode + Pfad → Callable. Parameter `{id}`. 404 / 405.

**PHP-Fokus:** `enum HttpMethod: string`. `match` für Method-Dispatch wenn es hilft. Regex vs. einfache Segment-Maps — nimm das, das du erklären kannst. Keine Magie.

**Du:** `GET /api/health` → Closure oder Array `[Klasse, 'methode']` die JSON zurückgibt. Noch kein DB-Ping.

**Fertig wenn:** Unbekannte URL 404 JSON; `POST /api/health` wenn nur GET registriert → 405; `{id}` kommt als Argument oder Request-Attribut an.

**Quellen:** [Enums](https://www.php.net/manual/en/language.enumerations.php) · [match](https://www.php.net/manual/en/control-structures.match.php)

**Nicht:** Middleware-Pipeline, Attribute-Routing (kann später).

### Phase 1.4 — Controller

**Ziel:** App-Code in `App\Controllers\…`, Kernel in `Framework\`.

**PHP-Fokus:** Klassenmethoden mit typisiertem `Request $request`: `Response`. Instantiierung erst einfach (`new`), Container erst wenn du Abhängigkeiten wirklich brauchst.

**Du:** `routes/api.php` registriert `HealthController::show`. Controller `return Response::json([...])`.

**Fertig wenn:** Neue API-Methode = neue Controller-Methode + eine Zeile Route, Kernel unverändert.

---

## Welle 2 — Persistenz

### Phase 2.1 — Env und Config

**Ziel:** DSN, User, Passwort nicht im PHP-Quelltext.

**PHP-Fokus:** `private` Parser, `static` nur wo es Config wirklich global ist — oder ein `Config`-Objekt aus Application. Kein `extract()`.

**Du:** `.env` laden (selbst oder eine **kleine** lib, die du verstehst). `config/database.php` gibt Array für PDO. Nie `.env` committen.

**Fertig wenn:** Host/Port/DB/Name/Passwort ändern ohne PHP-Klassen anzufassen.

**Quellen:** [putenv/getenv](https://www.php.net/manual/en/function.getenv.php) — oder Dotenv-Doku, falls du eine lib nimmst.

### Phase 2.2 — PDO-Connection

**Ziel:** Eine Connection-Klasse, DSN `pgsql:…`, `ERRMODE_EXCEPTION`, Prepared Statements.

**PHP-Fokus:** Dependency: Application hält `PDO` oder `Connection`. Exceptions, nicht `or die()`.

**Du:** `GET /api/health` macht `SELECT 1` (ping). Fehler → JSON 500, keine HTML-Warnung.

**Fertig wenn:** Compose-Postgres down → klarer Fehler; up → health ok. Nirgends String-SQL mit Interpolation.

**Quellen:** [PDO](https://www.php.net/manual/en/book.pdo.php) · [PDO::prepare](https://www.php.net/manual/en/pdo.prepare.php) · [Connections](https://www.php.net/manual/en/pdo.connections.php)

### Phase 2.3 — Erstes Schema + Model

**Ziel:** Eine Übungs-Ressource (z. B. `notes`: id, title, body, timestamps) — **nicht** das CMS.

**PHP-Fokus:** Model als Klasse um eine Tabelle, typisierte Getter oder readonly DTOs nach `fetch`. `declare(strict_types=1)` + PDO `FETCH`-Arrays in Objekte überführen (Casts bewusst).

**Du:** SQL-Datei `storage/schema.sql` (Baby-Migration; echtes Migrationssystem = späterer ADR). Von Hand oder Mini-CLI anwenden. `GET/POST /api/notes`, `GET /api/notes/{id}`. POST: JSON-Body, `title` Pflicht — sonst 422 JSON, kein stilles Leerspeichern.

**Fertig wenn:** Note anlegen und lesen über HTTP, Daten in Postgres sichtbar, SQL nur prepared.

**Nicht:** Page-Builder, User-Auth, Datei-Upload.

---

## Welle 3 — Härten

### Phase 3.1 — Fehler und JSON-Konsistenz

**Ziel:** Ungefangene Exceptions und 404 sehen gleich aus (`{ "error": "…", "status": n }`), kein Stacktrace in Produktion (`APP_DEBUG`).

**PHP-Fokus:** `Throwable`, `try/catch` in Kernel, eigene Exception-Klassen (`NotFoundException`) mit typed Properties.

### Phase 3.2 — Middleware

**Ziel:** Pipeline: Request rein, Response raus. CORS für Vite (`localhost:5173`) vor Welle 4. Optional JSON-only für `/api`.

**PHP-Fokus:** Interface `MiddlewareInterface`, Callable-Kette. Keine globalen Flags.

### Phase 3.3 — Erste Tests

**Ziel:** PHPUnit (oder Pest, wenn du es lesen kannst) gegen Router/Controller **ohne** Browser. Ernstes Framework (0008).

**PHP-Fokus:** Autoload `tests/`, Namespaces `Tests\`.

**Fertig wenn:** Ein Test 404, einer Health oder Notes — `composer test` grün.

**Nicht:** 90 % Coverage erzwingen. Tests, die die echte Docker-DB brauchen, klar kennzeichnen oder für CI später; Kernel-Tests zuerst ohne Netz.

---

## Welle 4 — Erste SPA (von Hand)

### Phase 4.1 — Vite + React + TanStack Router

**Ziel:** Eigenes `npm`-Init im Ordner `frontend/` (ADR-0003: Composer fasst das nicht an). Eine Route `/notes`, `fetch('/api/notes')`.

**PHP-Fokus:** keiner — außer CORS/Proxy.

**Du:** Vite-Dev-Proxy `/api` → `localhost:8000`. File-based Route wie TanStack es vorsieht.

**Fertig wenn:** Liste der Notes aus Postgres in der Seite, ohne PHP-HTML.

**Quellen:** Vite · TanStack Router file-based routing · `fetch` + JSON

**Nicht:** `make:page`, Admin-CMS, Start/SSR.

### Phase 4.2 — SPA-Fallback

**Ziel:** Reload auf `/notes` in Produktion: PHP gibt `index.html` des Builds, nicht 404. `/api/*` bleibt API.

**Fertig wenn:** `npm run build` nach `public/…`, PHP unterscheidet Datei / API / Index.

---

## Welle 5 — DX (erst nach einer handgebauten Page)

### Phase 5.1 — `make:api`

CLI im PHP-Kern: Model/Controller/Route-Stub für eine Ressource. Templates, die **strict_types** und Namespaces enthalten.

### Phase 5.2 — `make:page` + TanStack-Adapter

Route-Datei im Frontend-Ordner (ADR-0005/0006). `package.json` erkennt `@tanstack/react-router`.

**Nicht:** Vue-Adapter oder React-Router, bevor dieser Adapter sitzt.

---

## Welle 6 — Sessions und Auth

### Phase 6.1 — PHP-Sessions (Dateien)

Login-State ohne User-Tabelle testen (z. B. Dummy), Cookie-Flags (`HttpOnly`, `SameSite`, später `Secure`). CSRF-Strategie für Cookie-Auth skizzieren (ADR wenn du wählst: Session-Cookie vs. Token).

### Phase 6.2 — Users in Postgres

Tabelle `users`, Passworthash (`password_hash` / `password_verify`, **kein** MD5). `POST /api/login`, `GET /api/me`, Logout. Writes auf Notes nur eingeloggt.

**Quellen:** [password_hash](https://www.php.net/manual/en/function.password-hash.php) · Session-Sicherheit

**Nicht:** OAuth, Firebase-Auth, CMS-Rollenmodell.

---

## Welle 7 — CMS-Flavor (ADR-0009)

Erst wenn 6.2 sitzt.

1. Tabelle `pages` (slug, title, status, `modules` JSONB).  
2. `GET /api/pages/{slug}` öffentlich.  
3. Writes nur Auth.  
4. Eine TanStack-Catch-all-Route + `ModuleRenderer` (ein Block-Typ reicht: z. B. `text`).  
5. Mini-Admin: Page anlegen, ein Modul setzen.  
6. Optional `make:block` später.

A11y in **deinen** Blöcken (0010). Kein Sanity.

---

## Welle 8 — Produkt und Verpackung

- Sitemap + `robots.txt` aus öffentlichen Slugs (0010).  
- MySQL-Treiber als zweiter PDO-DSN (0007).  
- Create-Project: Composer-Paket aus *diesem* Gerüst; Init-Menü Compose vs. externe DB (0003/0007).  
- SSR/Start nur eigener ADR, kein stiller Umbau von 0004.

---

## Empfohlene Reihenfolge der nächsten drei Schritte

1. Phase 0.1 (Docker + Composer)  
2. Phase 0.2 (OOP lesen)  
3. Phase 1.1 (JSON `{"ok":true}` aus dem Front Controller)

CMS, Scaffold, Auth: nicht in diesen drei.
