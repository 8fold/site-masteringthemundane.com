# Shared PHP website page rendering

## Purpose and status

Develop a generic HTTP request -> PHP rendering -> HTTP response approach for
all 8fold PHP website projects. Apps supplies the current implementation
reference; `.com` is the initial proving ground because its primarily static
page rendering permits work on the rendering boundary without application
workflow dependencies.

Decisions established here are intended to become the generic approach, not a
separate `.com` architecture. Intentional differences should be minimal and
supported by concrete requirements. This document describes the rendering
pipeline, not the content or editorial structure of individual pages.

The original reference was apps' `/legal/` route at commit
`4605629129cf03ffd87588b8c31f85edf118f48a`, inspected October 24, 2026.
Apps paths below are relative to that repository. The design is being refined;
the proposed shared boundary is not yet implemented across either site.

## Scope

Cover physical route dispatch, required initialization, request-path handling,
metadata resolution, server-side composition, document serialization, and HTTP
response delivery.

Account/session resolution, authentication, tenant and subscription context,
billing, and terms-acceptance workflows are outside this document and the
generic rendering approach. A public static page must not acquire those
dependencies merely to use the shared renderer.

Page prose, visible titles and heading choices, dates, offerings, and other
page-specific editorial decisions are outside this document. Existing page
behavior remains protected during architectural changes; this work does not
authorize content or presentation redesign.

## Rendering pipeline

```text
HTTP request
  -> Apache: public/ DocumentRoot and physical route dispatch
  -> public/<route>/index.php
  -> required public-site initialization and Composer autoloading
  -> resolve request path and rendering metadata
  -> route selects and composes renderable inputs
  -> shared document shell composes common structure and assets
  -> HTML Builder serializes the document
  -> HTTP response: status, headers, rendered body
```

Physical per-route entrypoints remain lightweight composition roots: they
define route-specific document inputs and delegate shared structure and
serialization to reusable source code. A short route definition need not be
wrapped in a one-to-one page class; use a separate class when composition
warrants it. This is not a single-front-controller design. Source, dependencies,
authored inputs, and private generated artifacts remain outside `public/`.

## Current apps reference

The useful rendering trace through `/legal/` is:

1. `public/legal/index.php` includes `config/bootstrap-web.php`, which loads
   the application's initialization and autoloading.
2. `Request` represents the incoming request and locates rendering metadata.
   `Request::titleStack()` reads ancestor metadata through
   `PageMetadata::fromFile()`; `PageTitle` receives the resulting values.
3. The route composes renderable inputs, including an existing compiled
   fragment selected through `ContentFragment::forRoute()`.
4. `DocumentBase` receives those inputs and composes the common document
   structure, navigation, head assets, and footer.
5. HTML Builder's `Document` serializes the composed structure for output.

These are existing implementation reference points, not prescribed class names
or APIs. The shared design must separate rendering from apps' application
initialization dependencies rather than copy the existing bootstrap wholesale.

The reference also has request-path caveats: `Request::pageDirectory()` assumes
a trailing slash, and `titleStack()` removes empty values with `array_filter()`
despite its comment describing visible empty segments. These observations are
not generic contracts to reproduce. Path handling and metadata validation need
explicit behavior before any implementation is reused.

## Proposed shared responsibilities

| Boundary | Responsibility |
| --- | --- |
| Web server | Dispatch physical routes and apply server-level redirects, headers, and error handling. |
| Initialization | Load only dependencies required by public rendering. Do not introduce application workflows as renderer prerequisites. |
| Request handling | Distinguish the URL path from query parameters and validate the route used for metadata and fragment lookup. |
| Metadata resolution | Resolve and validate rendering metadata independently of document composition. |
| Route composition | Select and compose renderable inputs, including compiled fragments where applicable. |
| Document shell | Compose shared document structure and assets from supplied inputs rather than choose a page's fragment itself. |
| Serialization | Produce HTML with explicit text/attribute escaping and deliberate treatment of trusted generated markup. |
| Response delivery | Return the intended status, headers, and body; expose missing or invalid rendering inputs rather than disguise failures as successful empty pages. |

These are responsibility boundaries, not a requirement for one new class per
row, a configurable framework, or a shared package. Prefer the smallest
implementation that establishes them.

Precompiled fragments are inputs to rendering. They are generated before
deployment, committed, checked for freshness, and deployed as-is; request-time
Markdown conversion is not part of this pipeline. Build commands and compiler
details remain in their existing executable and repository-specific homes.

## Pilot and migration

Use `/services/` on `.com` as the first implementation slice. Its role is to
exercise the generic boundaries, not establish permanent site-specific APIs.

The `/services/` physical entrypoint is the route composition root. It resolves
metadata through `PageMetadata::forRoute()`, selects the compiled fragment
through `ContentFragment::forRoute()`, and supplies those inputs to
`MTM\Shared\UserInterface\DocumentBase::create()`. `DocumentBase` is a
stringable builder with replaceable `headCss()`, `headJs()`, `introContent()`
and `content()` slots. It serializes the shared shell only when converted to a
string; page-specific markup may be supplied as `Eightfold\HtmlBuilder\Element`
values such as `Element::h2('Task')`, which makes the rendered element explicit,
or as trusted, already-rendered HTML.
Metadata text is escaped by the builder, while supplied markup is inserted
without escaping it a second time. The class owns no route discovery or content
selection. There is no corresponding `ServicesPage` class: the route definition
is visible beside its public URL, while the reusable document shell stays in
source.

Other routes retain `PageMetadata::render()` as a transitional composition
adapter. It supplies the fragment explicitly to the same shell, preserving their
existing behavior without migrating their route composition in this slice.
The current pilot changes no request-path handling, initialization, or response
policy. Those boundaries remain proposals until implemented and verified.

Preserve existing URLs, response behavior, escaping, metadata output, assets,
navigation, and rendered page behavior while changing those responsibilities.
Do not combine the architectural refactor with editorial or visual changes.
Remove obsolete adapters only when their responsibilities have moved.

Review the pilot before migrating the remaining routes in bounded slices.
Update this document alongside each accepted architectural decision, clearly
distinguishing proposed responsibilities from implemented behavior. Validate
generic reuse against apps before treating the approach as established across
all sites.

## Verification

Use actual HTTP requests to verify canonical public views return the intended
status and HTML response. Protect the shared document invariants with the
identical generic HTML assertions already used by both sites, and retain
page-specific checks where they protect behavior not established by the
generic HTTP checks.

Exercise missing or malformed rendering inputs and relevant request-path edge
cases. A successful renderer call alone does not establish correct HTTP
delivery. PHP's isolated loopback server can exercise physical page routes but
does not verify Apache `.htaccess` behavior; report that distinction explicitly.

Use each repository's documented validation entrypoints. Do not infer database
setup, remote-test prerequisites, or shared-host changes from this design.
