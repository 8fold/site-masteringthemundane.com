# 8fold Mastering the Mundane for HTML, CSS, JavaScript, and PHP

This website is for the individual coaching practice of [Josh Bruce](https://joshbruce.com) called [Mastering the Mundane](https://mastering-the-mundane.com).

This repository is a public display of [Trunk Based Development](https://trunkbaseddevelopment.com), [Evolutionary Architecture](https://www.thoughtworks.com/insights/decoder/e/evolutionary-architecture), and a loose interpretation of [Hexagonal Architecture](https://en.wikipedia.org/wiki/Hexagonal_architecture_(software)).

## Installation

1. [Clone the repository](https://docs.github.com/en/repositories/creating-and-managing-repositories/cloning-a-repository)
2. Start a locally hosted PHP web server (these instructions will use a free third-party application)
	1. Download [MAMP](https://www.mamp.info/en/downloads/) (not available for Unix or Linux)
	2. Install and launch MAMP
	3. Click preferences
	4. Click server
	5. Change the root directory to `/path/to/site-mastering-the-mundane.com/public` (or local, if available)
	6. Click OK
	7. Click Start (upper right on macOS or start servers on Windows)
	8. Your default browser should automatically launch, displaying a MAMP marketing page
	9. Remove everything after `/MAMP` in the URL and hit Enter

You should see the same Mastering the Mundane website that you could find on the public web. Not only that, but you should be able to navigate around just like you would on the public web.

## Contributing

AI coding assistants must read [AGENTS.md](AGENTS.md) and the repository-specific
[agent context](docs/agent-context.md). These distinguish shared operating rules
from this site's actual commands and infrastructure.

For the canonical home of new or changed information, see
[Information Hierarchy & Purpose](docs/architecture-philosophy.md#information-hierarchy--purpose).

Deployment and the owner-managed move from the former `site-root/public/`
DocumentRoot are documented in [deployment and cutover](docs/deployment.md);
the repository's `site-root/` directory has been removed, but external web-server
configurations may still need that owner-managed update.

### Reading pages: Coaching, Resources and exercises

Home uses the same shared shell, with four labelled h2 sections retaining the
Time, People, Space and Money fragment identifiers. Edit
`public_content/content.md` and regenerate its sibling `content.html`.
Root `public_content/meta.json` holds Home's `title` and `description`.
No synthetic `/home/` route is used.

#### Reading page metadata

Every shared-shell page reads its own `title` and `description` from the
`meta.json` beside its `content.html`: `public_content/<route>/meta.json` for
all nine converted pages. `PageMetadata::ROUTES` lists these routes.
Following the apps site's `PageTitle` convention, there is no separate heading:
the header h1 is the page's own `title`, and the browser and social title is
`PageTitle` of that title followed by each ancestor route's title, joined with
" | " (for example "Services | Mastering the Mundane"). Both fields must be
non-empty strings, and a `heading` field is rejected. `composer compile-content` and
`composer check-content` validate every route before preparing fragments, and
requests validate the page they render; failures name the `meta.json` path.
Values are HTML-escaped when rendered. Edit wording in `meta.json`, not in the
route entrypoints.
Each domain links to Coaching and Services. Copy draws on the operational
domains and calm, practical, autonomy-first voice in the apps repository's
`docs/brand-style-guide-2026v1.md` reference snapshot, without advertising
planned software or inventing domain-specific services. This is a content
rewrite, not a preservation-only migration.

`/coaching-and-mentoring/`, `/services/`, the `/resources/` index, `/resources/the-action-loop/`,
`/resources/motivators/`, `/resources/the-5-ps/`, `/books/` and
`/books/time-mastering-the-mundane/` adopt the apps site's
visual and architectural foundation. All other routes still use their existing
templates and stylesheet.
The Resources index discovers three explicitly selected published items from
their metadata. Its heading, Read navigation and descriptive metadata identify
Resources. Content and unrelated metadata remain unchanged.

Published resource entrypoints remain under `public/resources/`; their
sources, metadata and compiled fragments live under `public_content/resources/`.
The index collection and generated fragment live under `public_content/resources/`.
Explicit permanent redirects in `.htaccess` send `/articles/` and
`/tools-and-resources/` to `/resources/`, the old Action Loop URL to
`/resources/the-action-loop/`, and both old exercise URLs to their corresponding
`/resources/` destinations. Slashless and `index.php` variants redirect directly
too; query strings are retained. Redirects run before generic slash handling.
The old indexes are not separate copies of the collection.

Use PHP 8.5+ and run `composer install` from the repository root. Commit
`composer.lock` so local development and CI resolve the same application dependencies.
Apache's document root remains `public`; neither `src/` nor
`public_content/` belongs in the webroot. No database, session, environment file,
JavaScript build, or account system is required.

Edit `public_content/coaching-and-mentoring/content.md` for Coaching prose.
Edit `public_content/services/content.md` for Services prose. Each service uses
an h2 followed by its pricing, paragraphs and lists in ordinary Markdown;
requests use the generated sibling `content.html`, without a runtime converter.
Services preserves its offerings, prices, introduction link and URL. The shared
Menu includes Services immediately after Coaching and marks only the actual
current page.
The Resources fragment is generated from the published collection described below,
not hand-maintained Markdown. After changing either source, run:

```sh
composer compile-content
composer check-content
composer run style
composer run stan
composer test
```

#### Books catalog

`/books/` is a text-only catalog authored in `public_content/books/content.md`,
with sibling metadata, compiled HTML and generated structured data. It lists
Time: Mastering the Mundane and Triumph over Time, 1st Edition with Leanpub
purchase links, and Money: Mastering the Mundane as the current work in progress.
The author confirmed Money's status; both Leanpub pages showed purchase offers
when checked on October 22, 2026. No price or release-date promises are authored.
Each book's h3 and content sit in an accessible named section, using the shared
card treatment for sections beginning with h3.

This is a user-approved content rewrite/re-engineering, not a preservation-only
refactor: images, the old crowdfunding/hopper list and distribution plans are
removed from the catalog. The Time detail page at
`/books/time-mastering-the-mundane/` keeps its retailer links and prose.
The cover image and its caption are removed at the author's request.
Its private Markdown and metadata now use the same compiler and reading shell
as the catalog. The former split title is represented once as the header h1;
the browser title remains "Time: Mastering the Mundane | Books | Mastering the Mundane".
Direct website sales is a separate business/operations discussion; the catalog
does not add checkout, delivery, inventory or new distribution arrangements.

Commit the source changes and generated `content.html`. Compilation uses
portable per-file output, for example
`compiled (mastering-the-mundane.com): /public_content/coaching-and-mentoring/content.md -> content.html`.
The label is the fixed public hostname, not the local preview host. Conversion uses
only the Composer autoloader and trusted repository Markdown, allows its reviewed
inline HTML, and rejects unsafe Markdown link schemes.
Abbreviations use 8fold's inline syntax: `[.United States Dollars](USD)` renders
`<abbr title="United States Dollars">USD</abbr>`. Expansion is per occurrence,
not a document-wide replacement of every `USD`.
Markdown links to a different hostname, including subdomains, open in a new tab
with exactly `rel="noopener noreferrer"` and no added `nofollow`. Only the exact
production hostname `mastering-the-mundane.com` (case-insensitive), relative links
and fragments remain same-tab. This fixed build-time policy also works on local
preview without runtime parsing. The compiler explicitly enables CommonMark's
external-link extension; this behavior is not enabled by default. Reviewed raw
HTML anchors bypass that AST extension and must carry appropriate attributes
themselves. The compiler decorates `_blank` anchors with an inline, decorative
square-and-arrow SVG and visually hidden "opens in a new tab" text. The shared
Apps header link uses the same helper. Link text remains underlined; the icon
and its margin are not underlined. A no-wrap span keeps the final word and icon
together. No new-tab behavior or destination policy changes.
PHP's DOM extension is required for this HTML5-aware decoration; no JavaScript,
icon package or additional network request is used.
The complete rendered pilot document is DOM-checked by destination hostname,
including raw HTML and shell anchors, rather than selecting only links already
marked `_blank`. HTTP(S) external links must include both `noopener` and
`noreferrer` rel tokens; additional rel tokens are allowed. Internal links must
stay same-tab. Relative, query, fragment and protocol-relative destinations are
resolved against the document/base URL using PHP 8.5's browser-equivalent
`Uri\WhatWg\Url` API (`ext-uri` is required for development checks). Backslashes
and userinfo follow browser URL semantics; malformed URLs fail visibly rather
than being skipped. Non-HTTP schemes are outside the hostname policy.
Negative compiler-rendered fixtures prove that unsafe raw anchors fail this
assertion; the check does not sanitize them at runtime or migrate legacy pages.
Tests protect these link/rendering contracts, not editorial wording, prices,
paragraph counts or incidental wrappers.
It never publishes `content-root/` drafts or scans other legacy content.
The compiler discovers Markdown only within `public_content`:
edit `public_content/resources/{motivators,the-5-ps}/content.md`
and commit each generated sibling `content.html`. Source, metadata and generated
artifact move together without duplication. `ContentFragment` maps every route
to `public_content/<route>/content.html`; legacy directories outside that root
are not scanned.
The page's own title is the shared-header h1, so the page has one h1. Prose, dates, order and exercise instructions are unchanged.
Four straight apostrophes in Markdown were aligned with the curly apostrophes
already displayed in its legacy HTML, preserving the published text exactly.
The individual walkthrough stays unboxed. "For groups" is a named section
using the shared card treatment to distinguish the collective application;
its heading remains h2. The closing "One other thing" remains outside that
container because its guidance applies to either context.
The 5 Ps similarly keeps the individual walkthrough unboxed and places its
existing "For groups" application (including ordered instructions) in a named
h2 section container. Its own title is the shared-header h1; existing dates and prose remain unchanged. Three
apostrophes and a quotation pair in its Markdown are aligned with punctuation
already displayed in the legacy HTML.
Both exercises reuse the mobile-first publication dates: stacked by default,
Updated left and Original right from 70ch, with an Original-only date left-aligned.
Action Loop is also explicitly selected at
`public_content/resources/the-action-loop/content.md`, with its generated sibling
`content.html` and an explicit `ContentFragment` mapping. `ActionLoopPage` supplies
its sole h1; existing h2 sections, lists, links and editorial metadata are preserved.
Markdown apostrophes match those already displayed in its published HTML.
It reuses the same publication-date layout without correcting the existing
chronology discrepancy: Original June 23, 2024 is later than Updated February 2,
2024. This requires a separate editorial decision, not an inferred date.
Its `model`/`Time` classification is independent of its prose presentation.
The existing Markdown library's `descriptionLists()` extension preserves their
semantic definition lists; no new dependency or runtime converter is used.
The Resources compiler reports
`compiled (mastering-the-mundane.com): /public_content/resources/published.json -> content.html`.
`check-content` is read-only
and fails for missing or stale artifacts; CI runs it too. Production requests read
the compiled HTML without loading the development-only Markdown converter.

#### Published content metadata

`public_content/resources/published.json` is the explicit collection of
existing public routes for discovery on Resources. There is no metadata glob and
no automatic publication: adding a `meta.json`, including under `content-root/`,
does not include an item. A selected route must already have `index.php` under
`public`, plus `content.html` and `meta.json` under `public_content`.

All converted pages keep authored Markdown, metadata and committed compiled
fragments outside the webroot in route-mirrored `public_content`. Deploy that
directory alongside the PHP code; it is required at runtime. Public directories
retain entrypoints, assets and deliberate downloads. Removing the former
resource `content.md`, `content.html` and `meta.json` HTTP URLs is an intentional
developer-facing/delivery contract change (re-engineering), without compatibility
redirects. Canonical page URLs and legacy page redirects are unchanged.

The list is generated in descending `updated` date order, regardless of manifest
order. Equal dates sort alphabetically by `listingTitle` (case-insensitive),
then by route for deterministic ties. Sorting never expands the published
selection. The editorial update date reflects meaningful resource changes;
rebuilds, URL moves and shared CSS changes do not automatically update it.

Each selected item's `meta.json` supplies:

- `listingTitle`: a non-empty display title, taken from the existing public link
  or page title. It is independent of the page's browser `title`.
- `type`: exactly `model`, `exercise`, `guide`, `reference`, or `essay`.
  This describes the resource's primary purpose, not its delivery format:
  understand a model, perform an exercise, follow a guide, consult a reference,
  or consider an essay. Choose one primary type, even for hybrid resources.
- `domain`: exactly `Time`, `People`, `Space`, or `Money`.
- `tags`: a JSON list of non-empty topic strings; `[]` is valid. Tags are not
  another type/domain taxonomy and are not displayed or used for filtering yet.
- `updated`: a valid calendar date in `YYYYMMDD` format, rendered on a second
  line as "Last updated" with a machine-readable HTML `time` element.

There is no `format` field. Prose, downloads, video and audio do not change the
resource's type. Tags describe additional topics rather than duplicating type
or domain; an empty list remains valid.

The current collection is Action Loop (`model`, `Time`), Motivators
(`exercise`, `People`) and The 5 Ps (`exercise`, `People`), all with empty tags.
Action Loop's discussion of sustainable pace, Parkinson's Law and the model from
*Triumph over Time* supports its Time classification. Resources intentionally
provides unified discovery of models and exercises; old index and detail URLs
redirect to the consolidated collection.

Source content and unrelated legacy metadata fields are preserved. The three
`updated` values were explicitly reconciled with the dates already displayed in
published content: February 2, 2024 for Action Loop, and May 11, 2026 for both
exercises. Dates are read from metadata, never inferred or automatically chosen
by comparing sources. The resource details' legacy `title` values were copied
(`The 5 Ps` in all three) and never read; each now holds its exact prior h1:
"Action loop", "The Motivators exercise by Josh Bruce", or
"The 5 Ps exercise by Josh Bruce". This user-approved authoring-contract and
output change is re-engineering, not a pure refactor. The h1s are unchanged;
browser and social titles now use those concise leaves, followed by
"Resources | Mastering the Mundane". This also replaces the retired
"Tools and Resources" parent for Motivators and 5 Ps. Discovery `listingTitle`
values remain independent and unchanged.
Creation dates still need separate editorial review.
Malformed JSON, invalid required fields, unknown taxonomy values, duplicate or
invalid routes and missing published files fail the build with the source path.
All fragments are prepared before writing; `composer check-content` checks the
generated index as well as Markdown fragments without modifying files.
Discovery metadata is parsed only at build time; page titles and descriptions
are validated at request time. There is no new dependency, search or filtering.

#### Compiled structured data

`composer compile-content` also generates a committed private sibling
`structured-data.json` for each route in `PageMetadata::ROUTES`.
`composer check-content` verifies all 18 HTML/JSON artifacts without writing.
All artifacts are prepared and validated before any are written. Metadata
changes require regeneration; deploy the generated JSON alongside the HTML.

`StructuredData` builds a limited schema.org graph: `WebPage` from the page's
own validated title/description, `WebSite` named from root metadata, and shared
`Person` identity authored once in the generator. Only Coaching also describes
a `Service`. `CoachingService` is not a defined schema.org type (its schema.org
URL returned 404); `WebPage`, `WebSite`, `Person` and `Service` are defined types.
The generator uses an explicit production origin plus the route, never request
Host or the preview origin. It does not infer Article types, publication dates,
offers, geographic coverage or ratings from internal discovery taxonomy.

The shared shell embeds the saved JSON, safely encoded for a script element.
Runtime checks JSON shape and script safety, but never regenerates the graph or
compares it with source metadata. Missing, unreadable or invalid artifacts fail
explicitly with their path. Freshness belongs to build/CI `composer check-content`;
run `composer compile-content` after metadata or generator/provider/origin changes
and deploy the resulting artifacts together with the code.
This replaces the blanket service markup on converted routes only; visible
copy, browser/social metadata and discovery behavior are unchanged. No SEO or
AI-search ranking improvement is promised.

Legacy boundaries remain deliberately separate: `public/head.php`
still emits its old `CoachingService` literal for legacy pages such as `/time/`.
Both the Books catalog and Time book detail use generated WebPage graphs;
the detail migration adds no Book/Offer schema or direct-sales claims.
The orphaned resource `article.html` JSON-LD-only files were removed; they were
not inputs to the compiler or canonical converted pages. No compatibility
redirects were added. Their historical Article dates were not transferred.

The shared HTML shell is `src/Shared/UserInterface/DocumentBase.php`
(`MTM\Shared\UserInterface`);
navigation uses the shared `nav-header`, `nav-primary` and `nav-panel` selectors.
Header controls size to their content rather than reserving fixed side columns.
The current menu destination remains bold with a double underline.
Most converted routes currently use a small page adapter to bind the route to
`PageMetadata`; `/services/` is the composition pilot, with metadata, compiled
content selection and the shared shell call defined in
`public/services/index.php`. This keeps route-specific document composition
beside its public URL without duplicating the shell. Other routes have not yet
been migrated to this pattern.
`DocumentBase::create()` returns a stringable builder; page-specific headings
and other HTML Builder elements can be supplied through `content()` and
`introContent()`, while `headCss()` and `headJs()` replace their respective
asset slots.
`public/assets/css/reading.css` adapts
the apps legal page's executable CSS: 16px base typography, exact `70ch` main
width and responsive padding, heading defaults, footer,
focus and link styling. Main renders the compiled fragment directly,
without a marketing article wrapper. The header `h1` is the sole page title;
content subsections remain unboxed except the exercises' explicit group applications.
No acceptance form, third-party runtime font downloads or apps business-domain
dependencies are imported. A single reading delivery family tries exact local
Next face names, matching original Atkinson faces where available, then same-origin
Next URLs. Direct local family names do not precede that delivery family.
The installed Next faces are static, so CSS declares the actual shared weights
400, 500 and 700 separately for normal and italic, not a misleading variable
range for local Regular. Original Atkinson has no medium face; 500 tries local
Next Medium then hosted Next. Twelve declarations reuse two official variable
faces, normal and italic, without synthetic styles. Each file has Latin and Latin-ext
WOFF2 subsets; all four files total 110,864 bytes, use `font-display: swap`, and are
not preloaded. Browsers fetch only the styles/subsets actually used, not the entire
shipped set. Local font availability depends on the browser's privacy policy;
no browser setting change or Font Book extraction is needed.
Apache with `mod_headers` caches content-versioned CSS, JS and WOFF2 URLs for one year as
immutable; unversioned requests revalidate. Fonts retain their SHA-256 versions.
This policy does not imply an
observed browser cache hit, and the PHP development server does not apply `.htaccess`.
The accompanying SIL Open Font License is in
`public/assets/fonts/AtkinsonHyperlegibleNext-OFL.txt`.
The unmodified WOFF2 subsets come from the Google Fonts CSS API v7; its stylesheet is
`https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible+Next:ital,wght@0,200..800;1,200..800&display=swap`.
The included license and family metadata are from
`google/fonts/ofl/atkinsonhyperlegiblenext` at
`45e3d5269e4a17d823f6d84470bc084dc5ae46f6`; its upstream metadata identifies
the `googlefonts/atkinson-hyperlegible-next` source at commit
`7925f50f649b3813257faf2f4c0b381011f434f1`.
The exact WOFF2s identify Next version 2.001, the Next Project Authors'
2020-2024 copyright and `https://openfontlicense.org`; their variable weight axes
are 200/400/800 (minimum/default/maximum). The retained
[upstream license](https://github.com/googlefonts/atkinson-hyperlegible-next/blob/7925f50f649b3813257faf2f4c0b381011f434f1/OFL.txt)
permits commercial/noncommercial embedding, redistribution and modification,
including subsets/format conversion, with copyright/license retained, no
standalone font sale, no implied endorsement and OFL-licensed derivatives.
No Reserved Font Names are declared in that license. We redistribute Google's
existing subsets without further subsetting or conversion. This does not
relicense the legacy `Atkinson-Hyperlegible-*-102a.woff2` files, which retain their
separate no-alteration license; they remain unchanged for legacy pages.
CSS uses an xxh3 content-hashed URL, matching apps; missing content/assets fail explicitly. Existing page
metadata values and external booking behavior are retained, not a new SEO policy.

### Public assets

Following the 8fold web-project convention used by apps, authored stylesheets live
in `public/assets/css/` and scripts in `public/assets/js/` when needed.
`composer minify` uses the same MatthiasMullie CSS and JS minifiers as apps to
generate committed sibling `.min.css` and `.min.js` files, skipping existing
minified files and absent asset directories. The `matthiasmullie/minify`
development dependency supplies both minifiers; no additional package is needed
for JavaScript. Shared and legacy
PHP pages link these minified files with content versions and `blocking="render"`.
Run `composer check-assets` to verify freshness without writing; CI and
`composer production` include this check. Deploy generated assets as-is; production
does not require the development minifier.

UI SVGs live under `/assets/ui/`; this site's poster and book cover use
`/assets/media/`. Fonts and their licenses live under `/assets/fonts/` with unchanged
bytes, SHA-256 versions and font-selection rules. Apache redirects the former
`/fonts/`, `/css/`, `/ui/` and
`/media/` URLs to their corresponding `/assets/` locations with query strings
preserved, so existing inbound links, shared image URLs and cached stylesheets
continue to resolve. Old unminified stylesheet URLs redirect to the corresponding
authored CSS, not a removed file.

Cache-busting gives fresh HTML the updated stylesheet URL, but does not replace
CSS in already-open documents. Those documents can request additional font
styles or glyph ranges later. Redirects keep these lazy font requests working
without retaining duplicate binaries.

The branded `/favicons/` files, manifest and browser configuration remain
intact. `DocumentBase` uses the HTML Builder `Favicons` component to emit their
declarations. No JavaScript is currently shipped by this site.

The following cold payload comparison is from an isolated WebKit HTTP fixture at
640 CSS pixels, with local font candidates deliberately removed in **both**
columns to exercise hosted delivery. It is not production timing or proof that
an untouched local-first stack selected the hosted face.

| Reading page | Original hosted: requests / font bytes | Next hosted: requests / font bytes |
| --- | ---: | ---: |
| Coaching | 2 / 34,732 | 1 / 33,996 |
| Resources | 1 / 17,208 | 1 / 33,996 |
| Action Loop | 3 / 53,024 | 2 / 71,640 |
| Motivators | 3 / 53,024 | 2 / 71,640 |
| The 5 Ps | 2 / 34,732 | 1 / 33,996 |

Natural native Chrome checks of the final twelve-rule implementation at 640px
confirmed the same hosted request/byte matrix. All five local-first pages used
installed Next with zero font requests and rendered pixels distinct from their
system controls; paragraph geometry matched their hosted-only controls.
Only Latin subsets were requested in those pages. Normal variable delivery
includes regular, medium and bold together; italic adds a separate 37,644-byte
Latin file. Total shipped bytes (110,864 Next versus 109,980 original) are not
per-page network cost. In the same fixture, a repeat visit reused the immutable
Next file with zero transfer and only one server request across both visits;
that observation does not establish production or other browsers' cache behavior.
Untouched local-first embedded WebKit rendered system-font pixels without
downloading Next. A native headless Chrome 155 specimen naturally selected
installed Next for both local-first and local-only with zero font requests;
the hosted-only control fetched 33,996 bytes and rendered Next separately.
On the earlier regular-only `src: local(Next), url(Next)` specimen, the user
observed standalone Safari local-first and hosted-only as an exact visual match,
while local-only failed. This is visual evidence, not a network measurement.
The earlier direct-family production stack was not equivalent. In the final
production-equivalent full-style review, the user accepted Safari local-first
and hosted-only as visually matching, with local-only unavailable as expected.
The user accepted Chrome's spacing difference in the larger specimen paragraphs,
but not the introductory paragraphs; its magnitude and cause remain unmeasured.
These are user-observed visual results, not Safari network measurements or a
measured temporal font-swap shift.
The production-equivalent twelve-rule specimen in native headless Chrome at
640px naturally selected all six local styles without font requests. Its
hosted-only control fetched two files totaling 71,640 bytes, shared across all
three weights. All six rendered styles differed from system fallback.
Settled tops, heights, line heights and line counts matched for six samples and
two paragraphs at that viewport, although medium/bold glyph widths differed
slightly between installed static and hosted variable Next, both version 2.001.
That bounded comparison neither establishes the cause of the user's observation
nor measures temporal font-swap movement.

The pilot header adapts apps' native, JavaScript-free popover pattern: a Menu,
centered text-only heading, and a direct "Apps" link to the apps site.
Menu links to Home, Coaching, Services, Resources and Books; domain shortcuts
are not in navigation. Home includes Time/People/Space/Money sections. Rewriting
Home copy is deferred until the other pages have been converted; see
[#10](https://github.com/8fold/site-masteringthemundane.com/issues/10).
The current route has `aria-current="page"`
and the established bold/double-underline visual current-page indication:
regular links and the text-only Menu and Read buttons use a 2px underline that
increases to 3px on hover, while current
navigation links use a double 1px underline that increases to 2px on hover.
The footer contains copyright only; the original tagline is omitted on the pilot.

The local Copilot preview is
`https://com.mastering-the-mundane.copilot:8890/coaching-and-mentoring/`.
The `current` symlink points to an isolated feature worktree, not `main`.
MAMP Pro may resolve the symlink into a concrete DocumentRoot; verify the host
root when switching worktrees rather than assuming retargeting the link suffices.
Keep the original `.com` vhost unchanged.

Browser and external-boundary checks are documented in
`tests-manual/coaching-pilot.md`, `tests-manual/resources-index.md` and
`tests-manual/motivators.md` and `tests-manual/the-5-ps.md`.
Automated tests assert rendering, actions,
metadata, compiler determinism and freshness, asset hash behavior and legacy
route isolation rather than taking editorial snapshots.

If you feel so inclined to contribute back to improving the codebase, please do so.

Use [short-lived feature branches](https://trunkbaseddevelopment.com/short-lived-feature-branches/)
and [pull requests](https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/creating-a-pull-request).
`main` is the migration baseline, never a destination for direct commits.

Also, watching the first 8m 30s of [this talk](https://youtu.be/jeX4fan9xHI?t=173) should help.

## Details

It feels like we, the web development community, have a fair amount of limiting beliefs when it comes to web design and development. I'm sure part of that is on me and what I get exposed to. I'm sure it's also reinforced due to working with certain clients.

## Other

{links or descriptions or license, versioning, and governance}
