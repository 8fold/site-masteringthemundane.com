# Coaching pilot

Serve the feature worktree with Apache, PHP 8.5+ and the existing `.htaccess`.
Use a separately configured HTTPS preview host with trusted local certificate
and normal hostname resolution. Do not repoint the original site.

- Open `/coaching-and-mentoring/` with JavaScript disabled. Check the coaching
  heading, unchanged copy, fees, session durations and all meaningful links.
- At 320px, tablet and desktop widths, check readable text, wrapping navigation,
  no horizontal scrolling, and 200% zoom. Check Menu, centered text-only
  "Coaching and Mentoring" heading, and the "Apps" link placement.
  Menu is the only popover. Check OS light/dark appearance and forced colors.
  Fonts are local-first with same-origin Next downloads when
  the matching installed face is unavailable, then system fallbacks.
- Root prose lists and definition lists, including ordered exercise instructions
  directly inside the "For groups" prose section, use the same `--space-md`
  (1.5rem) block gap as paragraphs. Gaps between list items and definition pairs
  remain `--space-xs` (0.75rem). Check simple, nested and paragraph-containing
  items at narrow widths and enlarged text, including RTL. Paragraphs within an
  item retain their paragraph spacing; trailing paragraph/list margins must not
  double the inter-item gap. Menu controls and explicitly marked component
  lists (`is`, `role`, or direct `li[is]`) retain their existing spacing,
  including navigation inside main.
  No lead-in meaning is inferred from prose or punctuation; a Markdown-friendly
  treatment is tracked separately in
  [8fold/site-mastering-the-mundane.apps#354](https://github.com/8fold/site-mastering-the-mundane.apps/issues/354).
- Tab from the start: the skip link becomes visible, jumps to the focusable
  main landmark, and subsequent focus remains visible. Check header/home,
  labeled Menu navigation, the "Apps" external link, heading hierarchy
  and footer with a screen reader. Check the native Menu popover with
  Enter/Space and dismissal with Escape and outside click; focus should return
  to the invoking button after Escape.
  Coaching must have `aria-current="page"` and the bold,
  double-underline visual indicator (1px at rest, 2px on hover). Other links
  and the Menu button use a 2px underline at rest and 3px on hover. No other
  menu link is current.
- Menu destinations are Home `/`, Coaching `/coaching-and-mentoring/`,
  Services `/services/`, Resources `/resources/` and Books `/books/`; it has no
  Home domain shortcuts. The header's "Apps" link opens
  `https://apps.mastering-the-mundane.com` in a separate tab.
- Confirm the introduction booking anchor retains its exact destination,
  `_blank`, `noopener noreferrer`, and the new-window marker.
  Check all `_blank` markers are larger (1em), superscript, and match the link
  underline accent color in light/dark mode, including hover/focus. In forced
  colors, the underline and marker use system `LinkText`, without overriding
  the user's palette. Check enlarged text, wrapping
  and clipping at narrow widths. The CSS alternative text "opens in a new tab"
  is best effort; verify with browser/screen-reader combinations rather than
  assuming CSS generated-content support. Same-tab links must not show a marker.
  Confirm video conferencing is explained as a link sent by Cal.com when booking
  is confirmed, with no Gather reference or link.
  No booking, account creation, payment or form submission is part of this check.
- Check network requests on page load: only local HTML/CSS/favicon resources and
  any matching self-hosted WOFF2 font subsets when neither local Atkinson family
  is available. No third-party font, Cal.com/Gather, JS or embed requests should
  occur. Metadata URLs are unchanged descriptive values, not resources to fetch.
- Fetch `/booking` and `/booking/` without following redirects; preserve existing
  behavior for this pilot. Currently `/booking/` removes the slash first (301),
  then `/booking` redirects to `/coaching-and-mentoring/` (301).
- Compare non-pilot pages before/after: `/`, `/books/`,
  `/books/time-mastering-the-mundane/`, `/services/`,
  `/resources/the-action-loop/`, `/tools-and-resources/`,
  `/resources/motivators/`, `/resources/the-5-ps/`.
  Their rendered HTML, stylesheets and appearance must remain unchanged.
- Run the documented Composer checks. Recompile twice; committed HTML must stay
  identical. Changing the pilot CSS must change its `?v=` hash, and the stylesheet
  response must contain the intended CSS. Do not change metadata/canonicals,
  publish drafts or remove redirects as an incidental part of this pilot.

The Resources index now shares the reading shell and `reading.css`; it is no
longer a non-pilot comparison route. See `resources-index.md`. Historical
`coaching.css` URLs below record the initial pilot only.

https://com.mastering-the-mundane.copilot:8890/css/coaching.css?v=88c0f768290a72ae1067f15c1d135e12817f4dc62a00f30693b8a29aa3d50480
https://com.mastering-the-mundane.copilot:8890/css/coaching.css?v=878788938ad55a365cbf20c789975ec273f4a08b88c24348041a277448114814

## Human review: October 21, 2026

These results describe the initial pilot before the requested copy/compiler and
navigation revisions; they are not a claim that subsequent revisions were reviewed.

| Check | Human result |
| --- | --- |
| JavaScript disabled | Pass |
| Light/dark appearance and fonts | Pass |
| Wrapping navigation | Pass; requested apps-style Menu, heading and Read pattern; later navigation redesign is documented above |
| Tab navigation | Pass; requested an apps issue for a visual current-page indicator alongside `aria-current` |
| Introduction and links | Pass; requested removal of Gather and explanation of Cal.com confirmation link |
| Page-load network requests | Unverified by human; appeared to pass, but reviewer was unsure how to verify |
| Legacy booking redirect | Pass for arrival at `/coaching-and-mentoring/`; individual hops not independently verified by human |
| Non-pilot appearance | Pass; other pages retain previous styling |
| Content compilation | Pass; requested apps-style per-file source-to-target output |
| CSS content hash | Pass; reviewer supplied the two versioned stylesheet URLs above |

The earlier browser resource inspection separately observed only local
CSS/logo/manifest requests, without JavaScript, webfonts or external-service
loads. That observation predates the self-hosted font fallback and does not
establish the current request set. Screen-reader, forced-colors and full
browser-zoom results were not reported.

## Requested revisions

Gather has been removed; Cal.com confirmation now explains delivery of the video
conference link. Compilation reports each source-to-target pair, retaining the
read-only freshness check. Navigation now uses apps-style native Menu/Read
popovers with no application JavaScript and the approved Home-section anchors.
The human results above predate these revisions and need a new navigation check.

The reading layout now follows apps `/legal/`, not the initial marketing scale:
16px body text with 24px line height, default 24px `h2` and 18.72px `h3`,
exact `70ch` main with 16px inline padding below the `70ch` media breakpoint
and zero inline padding above it. The header `h1` is the only page title;
main starts with introductory paragraphs, followed by unboxed content
subsections. There is no duplicate page heading or content-container
wrapper. Footer spacing and column alignment follow apps, with copyright only;
the tagline is removed. No account context or acceptance form is imported.

## Next font fallback review

Review the untouched local-first stack in standalone Safari, Chrome and Firefox
with a fresh cache before using any font API or diagnostic hosted-only control.
Record the rendered zero glyph and observed font requests, not just computed CSS,
`document.fonts.check()` or a successful explicit `document.fonts.load()`.
An embedded WebKit specimen can claim local availability while painting system
glyphs and never fetching the hosted fallback; do not extrapolate that to
standalone Safari or mark the automatic fallback accepted from a forced load.

The delivery family uses exact static local Next faces for each declared weight,
then original Atkinson for 400/700 where available, then Next version 2.001
variable WOFF2 URLs. Twelve normal/italic declarations cover 400/500/700 and
Latin/Latin-ext, sharing four files. No direct local family name precedes them.
Use the same complete production declarations in the local-first specimen;
hosted-only removes local sources, and local-only retains only installed Next
sources. Check regular 400, medium 500, bold 700 and all three real italics;
500 should differ from 400. Confirm the
crossed zero, readable fallback during a bounded delayed/throttled cold load and
on intentional font failure, narrow layout, wrapping and no horizontal overflow.
Report layout shifts, cache conditions and fixture throttling separately from
actual production performance. No preload or cross-origin asset sharing is added.

The user's earlier regular-only specimen review found Safari local-first and
hosted-only visually identical, with local-only unavailable. No Safari network
measurement was reported. Chrome hosted-only spacing looked different, but its
magnitude and cause were not measured. At identical viewport/content, compare
settled element tops, heights, line heights and line counts; distinguish
cumulative flow offsets from temporal movement during a font swap.

The final production-equivalent full-style comparison was accepted by the user:
Safari local-first and hosted-only visually matched, while local-only remained
unavailable as expected. Chrome's spacing difference appeared only in the larger
specimen paragraphs, not the introductory paragraphs, and was accepted. The
user said, "I think we can call this a success." This records visual acceptance,
not a Safari network measurement or a quantified explanation of Chrome's spacing.
