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
	5. Change the root directory to `/path/to/site-mastering-the-mundane.com/site-root/public` (or local, if available)
	6. Click OK
	7. Click Start (upper right on macOS or start servers on Windows)
	8. Your default browser should automatically launch, displaying a MAMP marketing page
	9. Remove everything after `/MAMP` in the URL and hit Enter

You should see the same Mastering the Mundane website that you could find on the public web. Not only that, but you should be able to navigate around just like you would on the public web.

## Contributing

### Reading pages: Coaching and Articles

`/coaching-and-mentoring/` and the `/articles/` index adopt the apps site's
visual and architectural foundation. All other routes still use their existing
templates and stylesheet. Legacy redirects are unchanged.
Individual articles, resources, Home and Books are not migrated.
The Articles index corrects its inherited Time/Tools and Resources labels to
Articles, with a short matching description. Open Graph and Twitter titles and
descriptions follow those corrected values; other metadata and SEO policy are
unchanged. The index now discovers three explicitly selected published items
from their metadata; their content, rendering and public destinations are unchanged.

Use PHP 8.5+ and run `composer install` from the repository root. Commit
`composer.lock` so local development and CI resolve the same application dependencies.
Apache's document root remains `site-root/public`; neither `src/` nor
`public_content/` belongs in the webroot. No database, session, environment file,
JavaScript build, or account system is required.

Edit `public_content/coaching-and-mentoring/content.md` for Coaching prose.
The Articles fragment is generated from the published collection described below,
not hand-maintained Markdown. After changing either source, run:

```sh
composer compile-content
composer check-content
composer run style
composer run stan
composer test
```

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
themselves. Pilot CSS marks `_blank` links with a visible new-window symbol.
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
It never publishes `content-root/` drafts or recompiles older articles.
The Articles compiler reports
`compiled (mastering-the-mundane.com): /public_content/articles/published.json -> content.html`.
`check-content` is read-only
and fails for missing or stale artifacts; CI runs it too. Production requests read
the compiled HTML without loading the development-only Markdown converter.

#### Published content metadata

`public_content/articles/published.json` is the explicit, ordered collection of
existing public routes for discovery on Articles. There is no metadata glob and
no automatic publication: adding a `meta.json`, including under `content-root/`,
does not include an item. A selected route must already have `index.php`,
`content.html`, and `meta.json` under `site-root/public`.

Each selected item's `meta.json` supplies:

- `listingTitle`: a non-empty display title, taken from the existing public link
  or page title. This avoids using copied/inconsistent legacy `title` values.
- `type`: exactly `article`, `exercise`, or `reference`.
- `domain`: exactly `Time`, `People`, `Space`, or `Money`.
- `tags`: a JSON list of non-empty topic strings; `[]` is valid. Tags are not
  another type/domain taxonomy and are not displayed or used for filtering yet.
- `updated`: a valid calendar date in `YYYYMMDD` format, rendered on a second
  line as "Last updated" with a machine-readable HTML `time` element.

The current collection is Action Loop (`article`, `Time`), Motivators
(`exercise`, `People`) and The 5 Ps (`exercise`, `People`), all with empty tags.
Action Loop's discussion of sustainable pace, Parkinson's Law and the model from
*Triumph over Time* supports its Time classification. Articles intentionally
provides unified discovery of both articles and exercises while the existing
resources index and detail URLs remain available.

Source content and unrelated legacy metadata fields are preserved. The three
`updated` values were explicitly reconciled with the dates already displayed in
published content: February 2, 2024 for Action Loop, and May 11, 2026 for both
exercises. Dates are read from metadata, never inferred or automatically chosen
by comparing sources. Legacy titles and creation dates still need separate
editorial review.
Malformed JSON, invalid required fields, unknown taxonomy values, duplicate or
invalid routes and missing published files fail the build with the source path.
All fragments are prepared before writing; `composer check-content` checks the
generated index as well as Markdown fragments without modifying files.
There is no runtime metadata parsing, new dependency, search or filtering.

The shared shell is `src/Marketing/UserInterface/ReadingPage.php`;
`CoachingPage` and `ArticlesPage` supply their existing metadata, heading and
content route. Each entry point only loads Composer and renders its page.
`site-root/public/css/reading.css` adapts
the apps legal page's executable CSS: 16px base typography, exact `70ch` main
width and responsive padding, heading defaults, footer,
focus and link styling. Main renders the compiled fragment directly,
without a marketing article wrapper. The header `h1` is the sole page title;
content subsections remain unboxed.
No acceptance form, downloaded fonts or apps
business-domain dependencies are imported. CSS uses a content-hashed URL;
missing content/assets fail explicitly. Existing page
metadata values and external booking behavior are retained, not a new SEO policy.

The pilot header adapts apps' native, JavaScript-free popover pattern: Menu,
centered text-only "Coaching and Mentoring" heading, and Read. Menu links to Home,
Coaching, and Home's Time/People/Space/Money
anchors; Read links to Books and Articles. Home includes a placeholder People
section so `/#people` resolves. Rewriting Home copy is deferred until the other
pages have been converted; see [#10](https://github.com/8fold/site-masteringthemundane.com/issues/10).
The current route has `aria-current="page"`
and the established bold/double-underline visual current-page indication.
The footer contains copyright only; the original tagline is omitted on the pilot.

The local Copilot preview is
`https://com.mastering-the-mundane.copilot:8890/coaching-and-mentoring/`.
The `current` symlink points to an isolated feature worktree, not `main`.
MAMP Pro may resolve the symlink into a concrete DocumentRoot; verify the host
root when switching worktrees rather than assuming retargeting the link suffices.
Keep the original `.com` vhost unchanged.

Browser and external-boundary checks are documented in
`tests-manual/coaching-pilot.md` and `tests-manual/articles-index.md`.
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
