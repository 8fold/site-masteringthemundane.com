# The 5 Ps reading-page review

Verify that the saved HTTPS vhost DocumentRoot and actual response serve this
worktree before reviewing
`https://com.mastering-the-mundane.copilot:8890/resources/the-5-ps/`.
Do not alter the original `.com` host or assume the current symlink selects MAMP's
saved concrete root.

- Keep the original title as the sole shared-header h1. Confirm dates, prose,
  the five term/definition pairs, individual examples and ordered group steps
  retain their content and order.
- The individual walkthrough is unboxed; the existing "For groups" application
  has a labelled section container with h2. It is a collective application of
  the exercise, not another step in the individual walkthrough.
- Check 320px, tablet, desktop and 200% zoom; no overflow, paired definitions
  and root prose lists with a 1.5rem block gap matching paragraphs; the
  definition-pair and list-item gaps remain 0.75rem. Ordered instructions in
  "For groups" also have the paragraph gap before and after. Nested/component
  lists retain their existing spacing. Check 16px/24px typography and the 70ch
  reading column. Dates stack by default and align Updated left / Original
  right from 70ch.
- Check light/dark, forced-colors and screen-reader heading/definition-list
  announcements. Test skip-to-main, native Menu/Read with pointer and keyboard,
  Escape/outside dismissal and focus return. No navigation link should falsely
  claim to be this detail page; Read still leads to Resources.
- Follow the link from Resources and through its old URL's redirect. Recheck
  Motivators, Coaching and Resources; Home, Books and Action Loop remain unchanged.
  No external booking/submission is needed.
- Run `composer compile-content` twice, `composer check-content` and
  `composer production`; five fragments are checked. The compiler explicitly
  selects this source without scanning other legacy content or publishing drafts.
  Requests render committed HTML without the development Markdown converter.

Report automated checks, trusted HTTPS and manual browser/keyboard/AT results
separately. Automated rendering checks do not imply physical or AT signoff.
