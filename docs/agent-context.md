# .com agent context

Read this with [AGENTS.md](../AGENTS.md). This document records this repository's
actual implementation, not apps' operating environment.

## Source and scope

The agent rules were adapted from
[apps AGENTS.md at 4df8c21](https://github.com/8fold/site-mastering-the-mundane.apps/blob/4df8c21abf66ea1cae093c645fa02c9b7ee1b715/AGENTS.md)
and its separate `docs/agent-context.md`. Shared requirements are retained where
applicable; apps-only paths, startup commands and enforcement claims are not
portable facts.

## Layout and runtime

- PHP 8.5+, `ext-dom` at runtime; `ext-uri` is a development requirement.
- Apache DocumentRoot: this checkout's `site-root/public/`, not apps' `public/`.
- PSR-4 application namespace: `MTM\`, rooted at `src/`.
- Reading presentation: `src/Marketing/UserInterface/ReadingPage.php`.
- Private authored Markdown/metadata and generated HTML/JSON: `public_content/`.
  `PageMetadata::ROUTES` defines the converted routes.
- Public authored/minified CSS: `site-root/public/assets/css/`.
  Fonts/licenses, UI SVGs and site images use `/assets/fonts/`, `/assets/ui/`
  and `/assets/media/`. Legacy asset URLs redirect; do not remove compatibility
  incidentally.
- Branded `/favicons/` remains unchanged. The shared favicon convention is
  explicitly deferred.
- Physical PHP routes and some legacy templates remain. Do not convert unrelated
  pages or rewrite their behavior as agent-infrastructure cleanup.
- No application database, account provisioning, Stripe SDK, book-order ledger
  or secure-download service is implemented here. Direct sales is separately
  tracked in issue #45; reuse decisions do not authorize subscription coupling.

## Build, test and lint commands

`composer.json` is the source of truth for entry points.
`composer production` runs optimized autoload generation, both freshness checks,
PHPCS, PHPStan and PHPUnit. CI is `.github/workflows/php85.yml`.

The current local environment uses MAMP PHP 8.5.2:

```sh
export PATH="/Applications/MAMP/bin/php/php8.5.2/bin:$PATH"
php /Applications/MAMP/bin/php/composer production
```

Use that same Composer executable with `minify`, `compile-content`, `check-assets`,
`check-content`, `style`, `stan` or `test` as needed. Other environments can use
`composer` directly with PHP 8.5+. Do not assume the developer-specific MAMP path
exists in CI.

There is no `composer local`, database test runner, test database initialization,
graph-refresh command, error-page generator or installed repository Git-hook
workflow. Do not call these as if they exist.

## Enforcement boundaries

- `phpcs.xml`: PSR-12 with explicit exceptions and a 100-character line limit.
- `phpstan.neon`: level 9 over configured implementation, CLI and test paths.
- `phpunit.xml`: the existing test suite; follow its fixtures and helpers.
- Content and asset freshness are checked without writes by CI and production.
- Apps' MethodScopeConventionRule, RecordPurityRule, data-object marker tests
  and locked-migration checks are not installed in this repository.
- Existing tests contain `$this->` method calls, and legacy PHP does not
  uniformly use strict types. Guidance is not a claim of complete enforcement
  or permission to bulk-refactor these files.

## Preview and environment safety

The current saved local preview is
`https://com.mastering-the-mundane.copilot:8890`.
It is trusted MAMP HTTPS, not a loopback development server.
Verify its returned markup and versioned stylesheet against the current
checkout before relying on browser results.

Do not retarget MAMP, edit shared/root-owned vhost configuration, move `current`
symlinks or access another checkout to force the preview to match. If it is
stale, report the mismatch and request an explicit correction or use an isolated
loopback check; identify HTTP checks honestly as HTTP.

Do not copy apps' `.env-local`, hostname rewrite, MySQL initialization or
hard-coded main-checkout paths. This site's current tests and content/asset
build commands require no secret environment setup.

## Agent infrastructure review and recommended sequence

This adoption establishes a truthful entry point, not full apps infrastructure
parity. Next steps, in priority order:

1. Agree the reusable-policy versus repository-context boundary across both
   properties. Apps' current root file still embeds stack/startup assumptions;
   synchronize policy changes deliberately, not by overwriting either context.
2. Review `.github/coding-standards-and-styles.md` against these rules. Its older
   guidance includes blanket non-null advice and historic PSR commentary.
   Resolve intentional policy differences before adding automated enforcement.
3. Decide whether the same-class `self::` convention needs the apps PHPStan rule
   here. Inventory existing exceptions and PHPUnit/library requirements first;
   adopt a bounded rule/test slice rather than silently changing production code.
4. Evaluate a portable `composer local`/hook workflow only for actual .com needs:
   asset/content generation and freshness. Existing CI already checks freshness;
   graph creation, databases and secret copying are not prerequisites.
5. Decide whether a maintained knowledge graph provides enough benefit for this
   smaller site to justify generation, dependencies, cost reporting and freshness
   ownership. Until then, focused source navigation is valid.

No new hooks, custom analysis rules, graph tooling, database infrastructure or
shared package is introduced by this documentation slice. Separate approval
and a bounded implementation should follow the chosen next step.
