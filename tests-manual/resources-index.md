# Resources index continuity review

Serve this feature worktree's `site-root/public` with PHP 8.5+.
Do not assume the existing Copilot HTTPS vhost serves this checkout: its owner
must approve and verify a preview switch first. Do not change MAMP or the
`current` symlink as part of this slice.

- Open `/resources/` with JavaScript disabled. The sole h1 identifies Resources;
  the browser title and short description identify the Resources index too.
  Open Graph and Twitter titles/descriptions must match their HTML counterparts.
  Other metadata and canonical policy remain unchanged.
  Check the published list identifies Action Loop as Model / Time and
  Motivators and The 5 Ps as Exercise / People. Follow the links to
  `/resources/the-action-loop/`, `/resources/motivators/` and
  `/resources/the-5-ps/`; each retains its content. Motivators now uses
  the shared reading presentation, as do The 5 Ps and Action Loop.
  No drafts appear.
  Each list item has a second "Last updated" line: February 2, 2024 for
  Action Loop and May 11, 2026 for both exercises.
  Items appear most recently updated first: The 5 Ps, Motivators, then Action Loop.
  Equal dates use case-insensitive title order, then route. Manifest order must
  not control presentation or change which resources are published.
- Check Menu and Read with keyboard and pointer: open using Enter/Space,
  dismiss using Escape or outside click, and verify focus return. Only Resources
  is current, in Read, with bold double-underline styling. Menu keeps Home,
  Coaching and the four existing Home domain anchors.
- At 320px, tablet, desktop and 200% zoom, check no horizontal overflow,
  centered text-only heading, 70ch reading column, native popovers, visible focus
  and skip-to-main behavior. Check light/dark and forced-colors appearance and
  screen-reader landmarks. Root prose lists use the same 1.5rem block gap as
  paragraphs; list-item gaps remain 0.75rem. The shell uses the same stylesheet
  as Coaching; recheck Coaching's navigation, booking link, prose spacing and
  appearance.
- Same-host and relative links stay same-tab. External HTTP(S) links, if added,
  including subdomains, need `_blank` and both `noopener` and `noreferrer`.
  Full-document automated checks use the shared WhatWG URL assertion.
  The shared CSS supplies the accent superscript new-tab marker; the index
  currently has no external content destination to mark.
- Check page-load requests: local CSS/favicons and, only when needed, self-hosted
  WOFF2 font subsets; no JavaScript, third-party font or embed, account or
  database requests.
- Run `composer production`, `composer compile-content` twice and
  `composer check-content`. All five generated fragments must be deterministic.
  Compiler stdout lists Coaching's `content.md` then Resources' `published.json`
  then the explicitly selected detail `content.md` files, with portable source paths and
  the fixed production hostname. Check stale and missing fragments fail rather
  than rendering success or silently compiling during requests. Automated isolated
  fixtures exercise malformed/invalid metadata, unknown type/domain, explicit
  published-only selection, HTML escaping and metadata-driven freshness.
- Check old `/articles/`, `/tools-and-resources/` and all three old detail URLs:
  slashless, trailing slash and `index.php` variants must return a single 301
  to the matching `/resources/` destination. Repeat with `?source=review`;
  confirm the query survives and the final response is 200. No canonical resource
  URL should redirect to itself or back to a legacy URL. Unknown legacy paths
  should not redirect to unrelated content.
- Compare non-target routes with the baseline: Home, Books and book detail,
  services and Coaching.
  Legacy styles, content, redirects and metadata must remain unchanged.

Browser, screen-reader, forced-colors and trusted HTTPS results must be reported
separately from automated/temporary loopback HTTP checks; none is implied here.
