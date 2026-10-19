# ADR-0014: Page contract — `run` / `render` / `send`, errors drop the page Response

- Status: Accepted
- Date: 2026-10-14
- Depends on: ADR-0012 (file routes, `IndexClass`), ADR-0013 (view + layout HTML)
- Amended by: [ADR-0016](0016-pagebuilder-layout-meta.md) (`render` delegates the HTML wrap to `PageBuilder`)
- Amends: how `run()` / `render()` talk to HTTP (sketches in `docs/http-flow.md` that returned a Response from `render` / `run`)

## Context

The page author should assemble **view name + data** (later: permissions). They should not build headers, status, or `echo`. Request/Response stay framework-owned.

`render` names HTML generation, not “finish the HTTP message”. Status (200 vs 404/500) is only known after `run()` succeeds or throws. Reusing a half-filled page Response for an error page mixes two answers.

## Alternatives

1. **`run(): Response` / `render(): Response`** — short `IndexClass`, but the author must return HTTP objects.
2. **`render(): string`, `run()` wraps Response** — honest `render`, still forces the author to touch Response.
3. **Void `run` + void `render`** — Response lives on the base Controller; glue calls `send()` after `run()`. Exceptions abort the page Response; glue builds a **new** error answer.

## Decision

Option 3.

### Object lifetime (happy path)

1. Glue (`public/index.php`) captures `Request`, resolves `IndexClass`, `new IndexClass($request)`.
2. Base `Controller` constructor stores Request and **`new Response()`** (default status 200, empty body).
3. Glue calls **`$page->run(): void`**. The page class **must** implement `run()`.
4. Inside `run()`, the author calls **`$this->render(string $view, array $data = []): void`**. `render` turns the inner view plus layout (ADR-0016: PageBuilder) into an HTML string and **`setBody`** on the inner Response. No `echo` in the page class.
5. Glue calls **`$page->send(): void`**, which forwards to `Response::send()`. That is the only client output on the happy path.

The page author never receives or returns a Response. A `response(): Response` getter on the Controller is allowed for tests; glue does not need to name Response.

### What each method may know

| Method | Knows | Must not |
|---|---|---|
| `run()` | `$this->request`, later models / auth helpers | `echo`, `header()`, `http_response_code()`, constructing Response |
| `render()` | View name, `$data`, inner Response body (and later layout) | Routing, query/POST except values passed in `$data` |
| `send()` | Inner Response (status, headers, body) | Views, Request |

Default success status stays **200** unless the Controller later grows helpers (`redirect`, `notFound`). Missing route / missing class / missing `run` / unexpected failure → **throw**. Do not mutate the page Response into a 404/500 body.

### Errors

Glue `try/catch` around construct + `run()` + `send()`. On `Throwable`: **do not** `$page->send()`. Discard that Response. `catchException` (or a later error page) builds a **separate** answer from the exception code/message.

Expected “no such page / no such record” is a throw (404). An empty but valid page (search with no rows) is **200** + empty view, not an exception.

JSON/`src/api/`: same `run` + `send`; no `render`. File and base class: **ADR-0017** (`RouteClass` extends `ApiController`). Not a second front controller.

## Consequences

- `IndexClass` stays small: data + `$this->render(...)`.
- Tests can `run()` and read `response()->getBody()` / status without `send()`.
- ADR-0013 HTML rules still apply (`require`, `EXTR_SKIP`, no `$data['content']`). Assembly is ADR-0016. `render` stays the HTML step; it does not return Response.
- Glue must treat a failed `resolveRouteToClass` (`false`) as 404, not pass `false` into `class_exists`.
- Error HTML may keep today’s dedicated error views until a later error-page `IndexClass`.
