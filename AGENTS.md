# AI coding assistant rules

Read [Agent Context](docs/agent-context.md) alongside this file before working.
This guidance adapts the apps repository's `AGENTS.md`; repository-specific
commands, paths and enforcement belong in the context document, not in assumed
shared infrastructure.

These documents are maintained guidance, not a completed integration milestone.
Update rules and repository context alongside subsequent scoped changes when
their commands, paths, safeguards or enforcement change.

## Project and architecture

- All 8fold PHP websites use the repository's `public/` directory as the
  Apache DocumentRoot. Keep application source, dependencies and private
  content outside that directory.
- Let the web server own routing through physical directories. Use per-route
  `index.php` files under `public/` as lightweight composition roots: define
  route-specific document inputs there and delegate shared structure and
  serialization to reusable source code. Do not add a one-to-one page class
  solely to move a short route definition out of its entrypoint; use a separate
  class when route composition warrants it. Do not route all requests through
  a single application front controller. Apply this pattern across all 8fold
  PHP websites.
- Store private authored Markdown/metadata and generated HTML/JSON in
  `public_content/` across all 8fold PHP websites.
- Keep environment configuration under the repository's `config/` directory
  across all 8fold PHP websites. Commit a secret-free JSON template at
  `config/.env-example`; ignore actual environment files. An empty JSON object
  is sufficient when the application has no environment settings.
- Serve fonts and their licenses from `/assets/fonts/`, UI SVGs from
  `/assets/ui/` and site images from `/assets/media/` across all 8fold PHP
  websites, with files stored under `public/assets/`.
- Store authored and minified CSS in `public/assets/css/` and JavaScript in
  `public/assets/js/` across all 8fold PHP websites. Use `composer minify`
  with the MatthiasMullie development dependency to generate committed sibling
  `.min.css` and `.min.js` artifacts; skip existing minified files and absent
  asset directories.
- Mastering the Mundane's coaching and publishing website uses PHP 8.5+,
  Apache physical-directory routing and server-rendered HTML.
- Preserve accessibility, progressive enhancement and operation without
  application JavaScript. Target WCAG 2.2 AA.
- Preserve published URLs and content unless the requested change authorizes
  a change. Keep authored content, generated artifacts and runtime presentation
  separate.
- Do not import apps' authentication, subscriptions, database or billing
  architecture merely because presentation and operating conventions are shared.

## Validation policy

Across all 8fold PHP websites, `composer.json` is the source of truth for
build, test and lint command entry points.

Across these websites, `phpcs.xml` defines PSR-12 with explicit exceptions
and a 100-character line limit.
`phpstan.neon` configures PHPStan level 9 over implementation, CLI and test paths.
`phpunit.xml` defines the test suite; follow its existing fixtures and helpers.

- Tests: `composer test`.
- One test class: `./vendor/bin/phpunit --filter=TestClassName`.
- PHPStan level 9: `composer stan`.
- PSR-12 with repository exceptions: `composer style`.
- Generate committed minified CSS and JS: `composer minify`.
- Generate committed HTML and structured data: `composer compile-content`.
- Read-only freshness checks: `composer check-assets` and `composer check-content`.
- Full validation: `composer production` (alias `composer prod`).

Use the exact entry points in [Agent Context](docs/agent-context.md).
Do not invent setup or test commands. Regenerate affected artifacts before
validation; deploy them as-is rather than compiling at request time.
Across all 8fold PHP websites, CI must verify committed content and asset
freshness. Deploy the verified artifacts as-is, without regenerating them
on the production server.

Manual protocols in `tests-manual/` are part of the test bed. Keep directly
related instructions current and distinguish human observations from automated
checks and source-only fixtures.

Tests should protect outcomes, required facts/actions, escaping, security,
accessibility and negative cases. Do not pin editable prose, incidental wrappers
or attribute ordering without a specific contract. Classify a UI/copy failure
before changing assertions; never remove coverage or replace expectations merely
to obtain a green build. Test counts are not targets.

## PHP conventions

- New PHP implementation files use `declare(strict_types=1);`.
- Use `self::methodName()` for methods defined within the same class or trait;
  use `parent::methodName()` for inherited methods and reserve `$this->` for
  properties. Do not mass-refactor legacy exceptions as part of unrelated work.
- Production classes are final and do not extend other classes, except built-in
  exceptions. PHPUnit test classes necessarily extend `TestCase`.
- Immutable data objects, when needed, use final readonly classes with typed
  promoted properties. Apply `DataObject`, or its `ValueObject` or `Record`
  specialization, from `MTM\Shared\Attributes`; do not repeat the generic marker
  on a specialization. After construction, expose properties only, with no
  public methods or side effects. Get-only computed properties must be pure.
  Keep file/database access and factories in separate collaborators. Marker
  docblocks define the contract; importing markers does not add database
  infrastructure or authorize entity gateways.
- Import classes through top-level `use` statements. In non-namespaced entry
  points, global classes are already in scope; do not add no-op imports.
- Surface missing or invalid content/assets explicitly. Do not add
  success-shaped fallbacks that conceal deployment or input errors.

See `.github/coding-standards-and-styles.md` and the actual PHPCS/PHPStan
configuration. New guidance does not imply that custom apps enforcement rules
exist here or authorize a repository-wide cleanup.

## Knowledge navigation

If a maintained `graphify-out/graph.json` is available, query it first for broad
architecture or dependency questions. Verify relevant source before editing;
graphs can be stale. Otherwise use focused file/symbol searches.

This repository currently has no tracked graph or graph maintenance commands.
Do not invent them, require a full extraction at startup, or commit generated
graph churn as part of unrelated work.

## Session startup

1. Read this file and `docs/agent-context.md`; inspect Git status and preserve
   unrelated work.
2. Confirm the selected worktree and runtime. Never operate in another session's
   checkout or change the main checkout.
3. Use installed dependencies. Install from the lock file only when needed;
   do not run `composer update` as routine startup.
4. Before browser review, verify the preview serves the current checkout.
   Follow the preview constraints in Agent Context. Do not change shared MAMP
   configuration or symlinks to repair a preview silently.
5. Run validation appropriate to the requested change. Documentation-only
   changes do not require the PHP suite unless documented commands or behavior
   need verification.

There is no database or secret environment-copy gate for this site's current
implementation. Do not copy apps' startup environment or database commands.

## Voice

This communication convention supports [Solutions over Scapegoats](.github/contributing.md#engineering-philosophy): focus on the problem and its resolution rather than assigning blame.

Frame things impersonally — the work and reality matter, not the participants, individually or collectively. Artifacts, arguments, and errors belong to no one in the telling: "the tea cup is broken," not "you broke the tea cup"; "the point presented," not "your point"; "the README," even when one person wrote every line. "Your" survives only when giving credit — "to your point" as acknowledgment, never as preface to a rebuttal or counterargument.

## Interaction and worktree lifecycle

**Information placement:** Follow
[Information Hierarchy & Purpose](docs/architecture-philosophy.md#information-hierarchy--purpose):
use the first suitable canonical home and link elsewhere rather than maintaining duplicate facts or procedures.

1. Treat reported friction as primary evidence. Reproduce or inspect the
   reported state before explaining expected behavior.
2. Give alternatives and honest recommendations rather than reflexive agreement.
3. Completion claims require tool evidence from the same turn. Report
   `git --no-pager status --short`, run `git --no-pager diff --name-only`, and
   give the exact changed paths, including new files and renames. If no files
   changed, say "Not implemented yet" rather than claiming implementation.
   Report concrete blockers instead of making future-tense promises.
4. Worktrees can disappear when archived. Persist approved work appropriately;
   do not assume a preview remains valid after a session is archived.
5. When requesting a user file edit, name its absolute path and checkout.
   Never ask the user to guess among identically named files.
6. Opening and merging PRs are human-gated. Ask before opening; merge only on
   explicit instruction. At most one PR per session is open at a time, not one
   per repository. Once open, do not commit unrelated ticket work until that
   PR is merged and the branch is synced.
7. Inspect and follow the repository PR template. Keep changes scoped and
   distinguish local verification from deployed behavior.
8. **Contribution and triage conventions:** Follow the commit scope and message
   conventions in [.github/contributing.md](.github/contributing.md#commit-workflow),
   and apply its cumulative issue-triage policy in
   [Issue triage](.github/contributing.md#issue-triage-maintainer-policy).
9. **Push with every commit:** Keep the origin branch current with each commit;
   pushing also updates any open PR.
10. **Pre-PR review evidence:** Before opening a PR, verify that its diff
    contains only the intended change with
    `git --no-pager diff origin/main...HEAD --stat`, and report that output with
    the exact changed-file list in the current user-facing session.

## Sensitive files

Do not read, search or output actual `.env*` environment files, credentials or
secret configuration. The committed, secret-free `config/.env-example` template
is documentation and may be maintained; never populate it with real secrets.
Do not bypass exclusions with Git history, scripts or other shell tools.
Follow `.copilotignore` if present. Never copy secrets from apps or another
checkout as an assumed prerequisite.
