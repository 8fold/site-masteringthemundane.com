# Articles index continuity review

Serve this feature worktree's `site-root/public` with PHP 8.5+.
Do not assume the existing Copilot HTTPS vhost serves this checkout: its owner
must approve and verify a preview switch first. Do not change MAMP or the
`current` symlink as part of this slice.

- Open `/articles/` with JavaScript disabled. The sole h1 identifies Articles;
  the browser title and short description identify the Articles index too.
  Open Graph and Twitter titles/descriptions must match their HTML counterparts.
  Other metadata and canonical policy remain unchanged.
  Check the published list identifies Action Loop as Article / Time and
  Motivators and The 5 Ps as Exercise / People. Follow the links to
  `/articles/the-action-loop/`, `/tools-and-resources/motivators/` and
  `/tools-and-resources/the-5-ps/`; each retains its content. Motivators now uses
  the shared reading presentation; Action Loop and The 5 Ps remain legacy.
  The resources index remains available. No drafts appear.
  Each list item has a second "Last updated" line: February 2, 2024 for
  Action Loop and May 11, 2026 for both exercises.
- Check Menu and Read with keyboard and pointer: open using Enter/Space,
  dismiss using Escape or outside click, and verify focus return. Only Articles
  is current, in Read, with bold double-underline styling. Menu keeps Home,
  Coaching and the four existing Home domain anchors.
- At 320px, tablet, desktop and 200% zoom, check no horizontal overflow,
  centered text-only heading, 70ch reading column, native popovers, visible focus
  and skip-to-main behavior. Check light/dark and forced-colors appearance and
  screen-reader landmarks. The shell uses the same stylesheet as Coaching;
  recheck Coaching's navigation, booking link, prose spacing and appearance.
- Same-host and relative links stay same-tab. External HTTP(S) links, if added,
  including subdomains, need `_blank` and both `noopener` and `noreferrer`.
  Full-document automated checks use the shared WhatWG URL assertion.
  The shared CSS supplies the accent superscript new-tab marker; the index
  currently has no external content destination to mark.
- Check page-load requests: local CSS/favicons only, no JavaScript, downloaded
  fonts, external embeds, account or database requests.
- Run `composer production`, `composer compile-content` twice and
  `composer check-content`. All three generated fragments must be deterministic.
  Compiler stdout lists Articles' `published.json` then Coaching's `content.md`
  then Motivators' explicitly selected `content.md`, with portable source paths and
  the fixed production hostname. Check stale and missing fragments fail rather
  than rendering success or silently compiling during requests. Automated isolated
  fixtures exercise malformed/invalid metadata, unknown type/domain, explicit
  published-only selection, HTML escaping and metadata-driven freshness.
- Compare non-target routes with the baseline: Home, Books and book detail,
  services, the individual Action Loop article, resources and The 5 Ps.
  Legacy styles, content, redirects and metadata must remain unchanged.

Browser, screen-reader, forced-colors and trusted HTTPS results must be reported
separately from automated/temporary loopback HTTP checks; none is implied here.
