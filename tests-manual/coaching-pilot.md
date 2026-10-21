# Coaching pilot

Serve the feature worktree with Apache, PHP 8.5+ and the existing `.htaccess`.
Use a separately configured HTTPS preview host with trusted local certificate
and normal hostname resolution. Do not repoint the original site.

- Open `/coaching-and-mentoring/` with JavaScript disabled. Check the coaching
  heading, unchanged copy, fees, session durations and all meaningful links.
- At 320px, tablet and desktop widths, check readable text, wrapping navigation,
  no horizontal scrolling, and 200% zoom. Check Menu, centered text-only
  "Coaching and Mentoring" heading and
  Read placement, and each open native popover. Check OS light/dark appearance and
  forced colors. Fonts are local-first with system fallbacks, not downloads.
- Prose lists in main should have half the paragraph gap between items:
  `--space-xs` (0.75rem), with no extra trailing item gap. Check simple, nested
  and paragraph-containing items at narrow widths and enlarged text, including
  RTL. Paragraphs within an item retain their paragraph spacing; trailing
  paragraph/list margins must not double the inter-item gap. Menu/Read controls
  and explicitly marked component lists (`is`, `role`, or direct `li[is]`)
  retain their existing spacing, including navigation inside main.
- Tab from the start: the skip link becomes visible, jumps to the focusable
  main landmark, and subsequent focus remains visible. Check header/home,
  labeled Menu/Read navigation, heading hierarchy and footer with a screen reader.
  Check native popover buttons with Enter/Space and dismissal with Escape and
  outside click; focus should return to the invoking button after Escape.
  Coaching must have `aria-current="page"` and the bold,
  double-underline visual indicator. No other menu link is current.
- Menu destinations are Home `/`, Coaching `/coaching-and-mentoring/`,
  Time `/#time`, People `/#people`, Space
  `/#space`, and Money `/#money`. Read destinations are Books `/books/` and
  Articles `/articles/`. People now targets a placeholder Home section. Confirm
  `/#people` reaches its heading; final Home copy is deferred until the other
  pages have been converted.
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
- Check network requests on page load: only local HTML/CSS/favicon resources,
  no Cal.com/Gather requests, JS, webfonts or third-party embeds. Metadata URLs
  are unchanged descriptive values, not resources to fetch.
- Fetch `/booking` and `/booking/` without following redirects; preserve existing
  behavior for this pilot. Currently `/booking/` removes the slash first (301),
  then `/booking` redirects to `/coaching-and-mentoring/` (301).
- Compare non-pilot pages before/after: `/`, `/books/`,
  `/books/time-mastering-the-mundane/`, `/services/`, `/articles/`,
  `/articles/the-action-loop/`, `/tools-and-resources/`,
  `/tools-and-resources/motivators/`, `/tools-and-resources/the-5-ps/`.
  Their rendered HTML, stylesheets and appearance must remain unchanged.
- Run the documented Composer checks. Recompile twice; committed HTML must stay
  identical. Changing the pilot CSS must change its `?v=` hash, and the stylesheet
  response must contain the intended CSS. Do not change metadata/canonicals,
  publish drafts or remove redirects as an incidental part of this pilot.

https://com.mastering-the-mundane.copilot:8890/css/coaching.css?v=88c0f768290a72ae1067f15c1d135e12817f4dc62a00f30693b8a29aa3d50480
https://com.mastering-the-mundane.copilot:8890/css/coaching.css?v=878788938ad55a365cbf20c789975ec273f4a08b88c24348041a277448114814

## Human review: October 21, 2026

These results describe the initial pilot before the requested copy/compiler and
navigation revisions; they are not a claim that subsequent revisions were reviewed.

| Check | Human result |
| --- | --- |
| JavaScript disabled | Pass |
| Light/dark appearance and fonts | Pass |
| Wrapping navigation | Pass; requested apps-style Menu, heading and Read pattern; exact Menu domains pending clarification |
| Tab navigation | Pass; requested an apps issue for a visual current-page indicator alongside `aria-current` |
| Introduction and links | Pass; requested removal of Gather and explanation of Cal.com confirmation link |
| Page-load network requests | Unverified by human; appeared to pass, but reviewer was unsure how to verify |
| Legacy booking redirect | Pass for arrival at `/coaching-and-mentoring/`; individual hops not independently verified by human |
| Non-pilot appearance | Pass; other pages retain previous styling |
| Content compilation | Pass; requested apps-style per-file source-to-target output |
| CSS content hash | Pass; reviewer supplied the two versioned stylesheet URLs above |

The earlier browser resource inspection separately observed only local
CSS/logo/manifest requests, without JavaScript, webfonts or external-service
loads. That automated observation does not change the human network result to
Pass. Screen-reader, forced-colors and full browser-zoom results were not reported.

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
