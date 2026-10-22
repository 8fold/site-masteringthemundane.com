# Action Loop reading presentation

Visit `/resources/the-action-loop/` on the local preview.

- Confirm one "Action loop" h1 in the shared header and five h2 component sections.
- Confirm the five-component and three-battery ordered lists remain readable.
- At a narrow viewport, dates stack Updated then Original without overflow.
  At 70ch and wider, Updated aligns left and Original right.
- Dates intentionally retain the published discrepancy: Updated February 2, 2024;
  Original June 23, 2024. Do not infer a replacement.
- Check Menu/Read keyboard interaction and focus, including dismissal and return
  to the invoking button. Resources is not marked as the current detail page.
- The Books link stays same-tab; Parkinson's Law opens a new tab with the
  new-window indicator. Screen-reader users should encounter native lists,
  headings, labelled navigation and machine-readable times.
- Check light/dark mode, zoom, forced colors and installed local font behavior.
  No bundled font or download is introduced by this migration.
- Follow Resources to each published destination; confirm Coaching, both exercises,
  resources, Home, Books and Services remain accessible.

Automated coverage: `composer production` includes generated-artifact freshness,
page structure/metadata, full-document hostname link policy and legacy isolation.
