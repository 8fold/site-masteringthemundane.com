# Apps page rendering: `/legal/`

Source-grounded architecture handoff for the `.com` session.

Snapshot: `8fold/site-mastering-the-mundane.apps`, commit
`4605629129cf03ffd87588b8c31f85edf118f48a`, inspected October 24, 2026.
Paths below are repository-relative. This document describes existing behavior,
not a proposed migration. No runtime changes, database resets, or vhost changes
were made for this investigation.

## 1. The architecture in one view

Apps uses physical PHP routes, server-side component composition, and
precompiled authored content. It does not use a front controller or parse
Markdown during page requests.

```text
Browser GET /legal/
  -> Apache public/ document root, physical public/legal/index.php
  -> config/bootstrap-web.php
       -> Composer/application autoloading and bootstrap-headless.php
       -> environment, session, account/tenant/subscription context
       -> Request(REQUEST_URI, public_content root)
       -> shared terms gate
  -> route-specific composition in public/legal/index.php
       -> Request::titleStack() -> PageMetadata -> PageTitle
       -> optional acceptance forms
       -> explicit legal h2 + ContentFragment::forRoute('/legal')
  -> DocumentBase::__toString()
       -> stringify intro/content, then collect unclaimed messages
       -> shared head, navigation/application h1, main, footer
       -> HTML Builder Document -> echoed response
```

The other half runs before deployment:

```text
public_content/legal/content.md
  -> composer compile-content / cli/compile-content.php
  -> public_content/legal/content.html (committed generated artifact)
  -> read by ContentFragment at request time
```

## 2. Request entry and bootstrap

`public/legal/index.php:4` includes
`dirname($_SERVER['DOCUMENT_ROOT']) . '/config/bootstrap-web.php'`.
The deployment contract is therefore that `public/` is the document root;
private code and authored content are outside it.
`public/.htaccess` supplies security headers, static error-document mappings,
and asset caching; it does not implement a central application router.

`config/bootstrap-web.php` includes `autoload-mtm-htmlbuilder.php` and
`bootstrap-headless.php`. The headless foundation selects initialization,
constructs the environment from configuration, initializes encryption, and
sets up error fallback and request telemetry. The web layer constructs
`Request` from the incoming URI and the `public_content` directory, starts
the PHP session, resolves the account/session, tenant, and subscription, and
constructs `AccountContext`. Presentation receives this context rather than
performing account or entitlement lookups inside the shell.

Public reading does not mean this endpoint bypasses shared bootstrapping.
In particular, the bootstrap's session-resolution exception path logs the
failure, adds a generic message, and redirects non-home requests to `/`.
Thus `/legal/` does not have an unconditional database-independent 200 path.

## 3. Metadata and document title

The route calls `PageTitle::create($request->titleStack())`
(`public/legal/index.php:29-33`).
`Request::titleStack()` (`src/Shared/Http/Request.php:82-115`) walks from
the content directory to its ancestors and reads `meta.json` through
`PageMetadata::fromFile()`.

For canonical `/legal/`, the inputs are:

| Input | Existing value or role |
| --- | --- |
| `public_content/legal/meta.json` | Title `Unified Terms of Service and Privacy Policy`; `updatedAt` and `effectiveAt` both `2026-10-21` |
| `public_content/meta.json` | Site title `MtM - Apps` |
| `PageMetadata` | Typed metadata: title plus optional updated/effective/created `IsoDate` values |
| `PageTitle` | HTML Builder component receiving the nearest-first title array |

The browser title, application `h1`, and content `h2` are separate inputs.
The route explicitly emits `h2('Unified Terms of Service and Privacy Policy')`;
the shell does not derive a content heading from the metadata.

Implementation caveat: `Request::pageDirectory()` uses
`substr($this->path, 0, -1)`, not a general URL-path normalization function,
and `titleStack()` finishes with `array_filter($titles)`. Its comment describes
visible empty segments, but the executable code removes empty values.
The canonical trailing-slash example is the scope of this trace; query-string
and noncanonical-path metadata behavior should not be inferred from the
comment. This handoff does not propose or apply a fix.

## 4. Authored legal content and compilation

`public_content/legal/content.md` owns the long-form legal text, including
its displayed update/effective dates and inner section markup/headings.
Those displayed dates are authored text, not substituted from `meta.json`.

`cli/compile-content.php` is standalone and needs Composer autoloading, not
sessions or configured databases. It walks Markdown sources under
`public_content/` and `messages/` (excluding email Markdown), converts them
with `Eightfold\Markdown\Markdown`, enables tables and abbreviations, allows
authored raw HTML, rejects unsafe links, and passes output through
`NewTabLink::decorate()`. It writes sibling `.html` files.

`ContentFragment::forRoute('/legal')`
(`src/Shared/UserInterface/ContentFragment.php:24-58`) trims surrounding
slashes and defaults to `content.html`. Stringification resolves
`public_content/legal/content.html`, reads it, and returns the compiled markup.
A failed read throws rather than silently rendering an empty document.
It does not read metadata, parse Markdown, wrap a whole page, or generate the
page's heading. Its output is intentional HTML, not text to escape again.

Generated content is committed, checked for freshness, and deployed as-is.
Editing Markdown without regenerating the sibling artifact does not update
the content read by a request.

## 5. Route-specific composition and terms acceptance

`public/legal/index.php:16-38` initializes empty top/bottom acceptance slots.
When `AccountContext::isPendingTermsAcceptance` is true, it creates one `Form`
component and places it in both slots. The same component is rendered twice:
once as `introContent`, once after the legal fragment.

The form posts to `/legal/accept/`, has accessible label `Accept Terms of
Service`, and contains an explicitly typed `I accept` submit button.
`src/Shared/UserInterface/Form.php` supplies the default POST method,
session CSRF hidden input, and return-path hidden input. Anonymous accounts
and accounts current on terms receive no acceptance form.

`AccountContext::isPendingTermsAcceptance` delegates to
`LatestTerms::haveNotBeenAcceptedBy()` (`src/Legal/LatestTerms.php:29-64`).
The policy compares acceptance time with the legal metadata's `updatedAt`,
not `effectiveAt` or the date prose in Markdown. That metadata is therefore
both presentation input and an onboarding-policy input.

The global gate in `bootstrap-web.php` confines pending accounts to
`/legal/`, `/legal/accept/`, and `/account/sign-out/`, redirecting other
paths with 303. The acceptance endpoint
`public/legal/accept/index.php` independently requires an account, POST,
and valid CSRF, records acceptance, and redirects to `/account/` with a
confirmation; a recording failure returns to `/legal/`. Anonymous requests
to the endpoint redirect to `/subscribe/`.

## 6. Shared document rendering

`DocumentBase::create()` accepts `PageTitle`, `AccountContext`, `Request`,
and `Application` (`src/Shared/UserInterface/DocumentBase.php:54-69`).
The legal route explicitly uses `Application::Core`.
The enum supplies label `Mastering the Mundane Apps`, slug `core`, and
domain `time` (`src/Billing/Application.php`).

At stringification (`DocumentBase.php:154` onward):

1. Intro content and body content are stringified once in that order.
   This permits form fields to claim keyed warnings before the shared message
   container consumes unclaimed messages.
2. Main contains messages, intro, then content, with `id="main"`,
   `tabindex="-1"`, and `is="core"`.
3. HTML Builder's `Document` receives the page title. The root gets
   `is="time"`; product pages otherwise follow the user's color-scheme choice.
4. Head includes the viewport meta tag, versioned `/assets/css/base.min.css`
   first, optional page styles/scripts, and the device-times module when
   account time-zone mode follows the device. The legal route itself supplies
   no custom head assets.
5. Body includes the skip link, `NavigationPrimary`, main, and footer.
   The application's single `h1` is in the shared navigation header.
   Legal's own title is the route-supplied `h2`.
6. Footer links to `/legal/` and renders HTML Builder's Copyright component
   for Mastering the Mundane, with starting year `2023`.

`NavigationPrimary` renders native Menu/Profile popovers and chooses
anonymous or signed-in links from `AccountContext`. It uses
`Request::isCurrentPage()` for exact destination matching.
`Stylesheet` delegates versioning to `AssetUrl::versioned()`; the server's
cache rules recognize hash-versioned asset URLs. No client-side hydration
is needed to render or read the legal text.

## 7. Verification boundaries and adaptation to `.com`

Existing evidence in source:

- `tests/HttpSmokeTest.php` includes expected anonymous `GET /legal/` 200
  and anonymous acceptance-endpoint redirects, plus a legal navigation check.
- `tests/HttpAuthenticatedSmokeTest.php` covers stale acceptance, the shared
  terms gate, and acceptance flow.
- `tests/CompileContentTest.php` exercises the content compilation boundary.
- `tests/HtmlDocumentAssertions.php` and `tests/HttpServerHarness.php`
  apply document-heading/ID assertions to reached HTTP responses.

These are references to existing checks, not a claim that they were rerun
for this research. No live-browser or deployed-Apache verification is claimed.

For `.com`, the reusable architectural idea is the separation of physical
route, request metadata, authored precompiled fragment, and shared document
shell. The apps-specific account bootstrap, terms gate, tenant/subscription
context, application identity, and acceptance form are existing apps behavior,
not requirements or recommendations for `.com`.

This document makes no recommendation to change runtime behavior. Before
copying code, compare `.com`'s own route/metadata contracts and shell API;
the trace describes apps at the pinned revision rather than promising
drop-in compatibility.
