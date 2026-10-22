# Motivators reading-page review

Verify the saved MAMP HTTPS DocumentRoot and the actual response before reviewing
`https://com.mastering-the-mundane.copilot:8890/tools-and-resources/motivators/`.
Do not assume the `current` symlink selects the saved concrete vhost root.
Do not change the original `.com` host.

- The original content title is the sole h1 in the shared header. Existing dates,
  prose, ordered instructions, two definition lists, abbreviation and h2 sections
  remain readable, with no duplicated title. The individual walkthrough remains
  unboxed; only "For groups" has a named section container, with its h2.
  "One other thing" remains a closing h2 outside the group container.
- Menu and Read use native popovers with JavaScript disabled. Check pointer,
  Enter/Space, Escape, outside dismissal, focus return and skip-to-main. No link
  falsely claims to be the current page; Articles still leads to the index.
- Check 320px, tablet, desktop, 200% zoom, light/dark and forced-colors; ensure
  terms and definitions remain associated, long prose wraps, and focus is visible.
  Root prose lists and definition lists have a 1.5rem block gap matching
  paragraphs; subsequent terms retain the 0.75rem gap between definition pairs,
  without adding space between a term and its definition or before the first
  term. Confirm the shared 16px/24px typography and 70ch column. Use a screen
  reader to check landmarks, heading order and definition-list announcements.
- Title, description and social metadata retain the prior values. No form,
  account, embed, third-party font or runtime Markdown request is introduced.
- Dates stack by default; at 70ch and wider they share a row with Updated left
  and Original right. Both retain visible labels and semantic `time` values.
  Check a single Original date stays left-aligned at both widths, and longer
  date labels wrap without overflow.
- Follow the Motivators link from Articles and the unchanged resources index.
  Recheck Coaching, Articles, Action Loop and The 5 Ps: only Motivators changes
  presentation. Do not book or submit anything.
- Run `composer compile-content` twice, `composer check-content` and
  `composer production`. The third portable compiler line names
  `/site-root/public/tools-and-resources/motivators/content.md -> content.html`.
  Keep both source and compiled artifact; requests load the artifact only.

Report browser, physical keyboard/assistive technology and trusted HTTPS checks
separately. Automated DOM and compiler checks do not imply manual signoff.
