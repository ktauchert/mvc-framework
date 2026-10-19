# Request, render, Response — file map

Not an implementation. After wave 1, responsibilities sit in these files (ADR-0014: page contract; ADR-0016: how HTML is assembled; ADR-0013: HTML rules). Request is data; `index.php` dispatches; `render()` writes HTML onto the inner Response; `$page->send()` is the only output on the happy path.

## Files

```
public/router.php                 PHP built-in server: static file or require index.php
public/index.php                  Front controller: capture Request, dispatch, send

Framework/Http/Request.php        Read $_SERVER / query / body once
Framework/Http/Response.php       status, headers, body; send()
Framework/Core/Resolver.php       URL path → IndexClass; dotted view → file
Framework/Core/Controller.php     Request + Response + MetaInfo + PageBuilder; render(); send()
Framework/Page/MetaInfo.php       title, description, css/js URLs, …
Framework/Page/PageBuilder.php    header/footer partials + page.php → HTML string

src/app/{path}/IndexClass.php     extends Controller; run(): void
src/views/{path}.php              HTML for <main> only (dots → folders)
src/layout/page.php               Document shell; slots $header, $content, $footer
src/layout/partials/_header.php   Default header
src/layout/partials/_footer.php   Default footer
src/styles/                       SCSS → public/css/app.css (ADR-0015)
mvc                               php mvc make:page (1.4); make:api (1.5) — ADR-0018
```

`component()` / `src/components/` come **after** the layout works (ADR-0013). Same idea: return HTML strings, still no `echo` in the page class.

## Who calls whom

```
router.php
  └─ require index.php
        ├─ Request::capture()                 // once
        ├─ Resolver::resolveRouteToClass(path)
        ├─ $page = new IndexClass($request)   // Response 200, empty body
        ├─ $page->run()                       // void; author calls render
        │     └─ $this->render('test.index', [...])  // void
        │           ├─ Resolver::resolveView
        │           ├─ buffer src/views/test/index.php → $content
        │           ├─ PageBuilder::buildFullPage     → HTML string
        │           │     ├─ partials/_header.php
        │           │     ├─ partials/_footer.php
        │           │     └─ layout/page.php (MetaInfo in <head>)
        │           └─ $this->response->setBody(html)
        └─ $page->send()                      // once; headers + body
```

On `Throwable`: do not `$page->send()`. `catchException` builds a separate error answer.

Glue should use `$request->getPath()` (or equivalent) for resolve, not a second `$_SERVER` parse. `resolveRouteToClass` returning `false` is a 404 — do not pass `false` into `class_exists`.

## Sketch of each role (signatures, not full code)

**`Request`** — “what did the client ask?”

- `capture(): self` from globals
- method / uri / path / headers (names as in code: `getMethod()`, `getPath()`, …)

**`Response`** — “what do we answer?”

- Constructor default 200 / empty body
- `setBody` / `setStatusCode` / `setHeader` used by the Controller, not by `IndexClass`
- `send(): void`

**`MetaInfo`** — “what goes in `<head>` and asset URLs?”

- Defaults (title, `/css/app.css`, `/js/app.js`, …)
- Page `run()` may change fields before `render`

**`PageBuilder`** — “shell + partials + `$content` → HTML string”

- Does not know Request or routing
- Layout sees `$content`, `$header`, `$footer`, and MetaInfo fields

**`Controller::render`** — “view locals → `<main>`, then PageBuilder, then `setBody`”

- `$data` is for the inner view only (`EXTR_SKIP`)
- Return type `void`

**`Controller::send`** — forwards to `Response::send()`

**`Controller::response()`** (1.3) — tests read the inner Response after `run()`; glue still calls `send()` only. Do not `send()` in PHPUnit.

**`IndexClass::run`** — “this page”

- Read what you need from `$this->request`
- Optional: set MetaInfo
- `$this->render('test.index', ['message' => '…'])`
- No `echo`, no `return` of Response

**`index.php`** — “glue”

- Capture → resolve class → `new` → `run()` → `send()`
- Catch exceptions → existing error views until 3.1

## Two kinds of Response (ADR-0017)

| Kind | File | Body from | Content-Type |
|---|---|---|---|
| Page | `src/app/{path}/IndexClass.php` | `render()` → PageBuilder + view | `text/html` |
| API | `src/api/{path}/RouteClass.php` | `ApiController` JSON helper (no `render`) | `application/json` |

Same Request, same glue `run()` / `send()`. Resolver: path `/api` or `/api/…` → `Api\…\RouteClass`; otherwise `App\Controllers\…\IndexClass`. API types and Resolver branch are **not built yet**.
