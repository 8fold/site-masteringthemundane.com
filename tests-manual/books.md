# Books catalog

Visit `/books/` on the saved HTTPS preview.

- The shared header says Books; browser/social titles read
  "Books | Mastering the Mundane". Menu marks only Books as current.
- Available books lists Time: Mastering the Mundane and Triumph over Time,
  1st Edition, each with a working Leanpub purchase link.
- Currently working on lists only Money: Mastering the Mundane, without a
  release-date promise, price or purchase link.
- No book images, crowdfunding appeals, publishing pipeline, or direct checkout.
- Each book is a card: a section labelled by its first h3, containing that book's
  description/status and links. Cards wrap without clipping at narrow widths.
- Verify keyboard navigation, skip link, external-link indication, light/dark
  modes, narrow-screen wrapping and no horizontal overflow.
- Follow "read more about Time": the existing URL now uses the shared reading
  shell, with one title, the original descriptive prose, no cover image and
  all four original retailer URLs. This preserves existing availability copy,
  not a new verification of retailer stock. No direct-sales button is added.
- On the detail page check 320px through desktop: no image or caption remains,
  purchase links keep their new-tab announcements and there is no overflow.
- The opening "Part of the..." paragraph is a blockquote using apps' surface,
  accent left border, small body type and snug line-height in light/dark modes.
- The catalog reads private compiled HTML, metadata and structured data; source
  URLs are not downloads. Regenerate after edits with `composer compile-content`
  and check freshness with `composer check-content`.
