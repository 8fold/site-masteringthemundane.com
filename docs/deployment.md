# Deployment (DreamHost / Apache)

This uses the same deployment workflow and script sequence as apps. Apache's
DocumentRoot is the repository's `public/`; source, dependencies and private
content remain outside it.

## Cutover from the old webhook and webroot

The owner confirmed the October 22, 2026 blank-page incident was a stale
autoloader: the old deployment webhook did not run Composer. The owner repaired
production. The `/public` move is layout alignment, not the diagnosed cause.

Before merging this migration, disable the old automatic deployment webhook
and preserve the working revision and host configuration. Do not run both
deployment mechanisms against the same checkout.

Prepare the reviewed checkout and its locked dependencies, then change the
hosting panel's DocumentRoot from `<repository>/site-root/public` to
`<repository>/public`. The old directory is not retained. This requires an
owner-managed hosting change; code cannot update the panel. Local MAMP likewise
needs an explicit user-managed DocumentRoot correction. Agents must not alter
shared hosts or symlinks silently.

Verify PHP 8.5+ for both web requests and noninteractive SSH, required runtime
extensions, Git and Composer. After cutover, manually check the homepage,
Books, Resources, static assets, error responses and private content boundaries.
Retain the known-good checkout/dependencies and old DocumentRoot information
for an owner-approved rollback if necessary.

## Apps-parity automation

`.github/workflows/deploy.yml` listens for successful completion of `PHP 8.5`
on main, serializes deploy jobs, installs the SSH identity and pinned host key,
then runs `./cli/deploy.sh` in the server checkout. As in apps, configure:

- Secrets: `DEPLOY_SSH_KEY`, `DEPLOY_KNOWN_HOSTS`.
- Variables: `DEPLOY_USER`, `DEPLOY_HOST`, `DEPLOY_PATH` (repository root).

Use a dedicated deploy user and verify the server host key independently.
The server checkout needs its own Git read access where required. Never paste
credentials into chat or commit them.
The header of `cli/deploy.sh` contains the adapted apps first-time setup
walkthrough: server checkout, noninteractive shell tools, deploy key, verified
host-key pinning, OPcache hook, Actions settings and deployment rehearsal.
Its example paths use `mtm-com`; substitute the actual production checkout.

`cli/deploy.sh` uses apps' step ordering and commands:

1. `git pull --ff-only`.
2. `composer install --no-dev --optimize-autoloader --no-interaction`.
3. `composer validate --no-check-publish`.
4. Lint `public/**/index.php`.
5. Run optional gitignored `config/deploy-server.sh`, then report the revision.

Concrete differences from apps: omit database migrations, practitioner account
provisioning and linting `config/bootstrap-headless.php`/`bootstrap-web.php`,
because .com has none. Setup guidance uses .com paths and has no apps database,
mail or Stripe configuration. The workflow's executable behavior is unchanged.

The Composer installation regenerates the production autoloader from the lock
file. Generated content and minified assets are committed and checked by the
existing .com CI commands; deployment does not regenerate them or require
development dependencies. The existing `composer deploy` script is a local
validation/optimization command, not the SSH deployment entry point.

Rehearse `./cli/deploy.sh` on the prepared server checkout before enabling
automation. The script must already exist there to be invoked over SSH.
The Deploy workflow must be on main; its activation is not proof that SSH
credentials or the host path are configured.

## Failure and cache behavior

As in apps, the script uses `set -euo pipefail` and names a failed step.
Deployment is in-place and fail-forward, not atomic; later failures may leave
new source or dependencies live. There is no automatic rollback or automated
HTTP smoke check. Workflow concurrency does not coordinate manual SSH deploys.

The CI-success event triggers deployment, but `git pull` takes current main;
it does not pin the triggering run's SHA. This is preserved apps behavior,
not a claim that the deployed revision is always the exact tested revision.
Changes to this behavior require a separately agreed cross-repo improvement.

For web OPcache, use timestamp revalidation or an owner-configured graceful
reload in `config/deploy-server.sh`. CLI cache reset does not clear the web
SAPI's OPcache. An absent hook warns but does not fail, matching apps.
Check versioned/unversioned CSS and font cache headers manually after deploy.
