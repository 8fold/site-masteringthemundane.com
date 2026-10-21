# The 5 Ps reading-page review

Verify that the saved HTTPS vhost DocumentRoot and actual response serve this
worktree before reviewing
`https://com.mastering-the-mundane.copilot:8890/tools-and-resources/the-5-ps/`.
Do not alter the original `.com` host or assume the current symlink selects MAMP's
saved concrete root.

- Keep the original title as the sole shared-header h1. Confirm dates, prose,
  the five term/definition pairs, individual examples and ordered group steps
  retain their content and order.
- The individual walkthrough is unboxed; the existing "For groups" application
  has a labelled section container with h2. It is a collective application of
  the exercise, not another step in the individual walkthrough.
- Check 320px, tablet, desktop and 200% zoom; no overflow, paired definitions
  with the shared list spacing, 16px/24px typography and 70ch reading column.
  Dates stack by default and align Updated left / Original right from 70ch.
- Check light/dark, forced-colors and screen-reader heading/definition-list
  announcements. Test skip-to-main, native Menu/Read with pointer and keyboard,
  Escape/outside dismissal and focus return. No navigation link should falsely
  claim to be this detail page; Read still leads to Articles.
- Follow the existing link from Articles and the resources index. Recheck
  Motivators, Coaching and Articles; Home, Books and Action Loop remain unchanged.
  No external booking/submission is needed.
- Run `composer compile-content` twice, `composer check-content` and
  `composer production`; four fragments are checked. The compiler explicitly
  selects this source without scanning other legacy content or publishing drafts.
  Requests render committed HTML without the development Markdown converter.

Report automated checks, trusted HTTPS and manual browser/keyboard/AT results
separately. Automated rendering checks do not imply physical or AT signoff.
