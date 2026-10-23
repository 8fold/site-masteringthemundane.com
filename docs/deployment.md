# Deployment (DreamHost / Apache)

This uses the same deployment workflow and script sequence as apps. Apache's
DocumentRoot is the repository's `public/`; source, dependencies and private
content remain outside it.

## Cutover from the old webhook and webroot

The owner confirmed the October 22, 2026 blank-page incident was a stale
autoloader: the old deployment webhook did not run Composer. The owner repaired
production. The `/public` move is layout alignment, not the diagnosed cause.

Before switching a production checkout to this deployment, disable any previous
automatic deployment webhook and preserve the working revision and host
configuration. Do not run both deployment mechanisms against the same checkout.

Prepare the reviewed checkout and its locked dependencies, then change the
hosting panel's DocumentRoot from `<repository>/site-root/public` to
`<repository>/public`. The old directory is not retained. This requires an
owner-managed hosting change; code cannot update the panel. Local MAMP likewise
needs an explicit user-managed DocumentRoot correction. Agents must not alter
shared hosts or symlinks silently.

Verify the web runtime against the [repository's PHP and extension
requirements](agent-context.md#layout-and-runtime). The deploy user's
noninteractive shell, tools and SSH prerequisites are documented in the
[deploy script setup walkthrough](../cli/deploy.sh). After cutover, manually
check the homepage, Books, Resources, static assets, error responses and
private content boundaries. Retain the known-good checkout/dependencies and
old DocumentRoot information for an owner-approved rollback if necessary.

## Apps-parity automation

`.github/workflows/deploy.yml` listens for successful completion of `PHP 8.5`
on main, serializes deploy jobs, installs the SSH identity and pinned host key,
then runs `./cli/deploy.sh` in the server checkout. That script's header is the
canonical home for deploy-user setup and prerequisites, Actions secrets and
variables, the deployment sequence, and optional host-specific hook setup and
behavior. Follow that setup walkthrough before enabling the workflow.

## Verify a deployed change

A green Deploy run proves the script completed, not that a changed release is
being served. A rerun against code already installed manually verifies the
automation can execute, but does not prove delivery of a new visible change.

For end-to-end verification, merge an intended, reviewed visible change through
the normal PR process without manually deploying it first. Watch that revision's
PHP 8.5 CI and Deploy runs, check the revision reported by the deployment log,
then confirm the expected change appears at the production HTTPS URL. Record
the observed content and HTTP result; HTTP 200 alone can still serve old code.
If the expected change is absent, investigate the checkout, DocumentRoot and
web OPcache/cache behavior rather than declaring deployment verified.

## Failure and cache behavior

Deployment is in-place and fail-forward, not atomic; later failures may leave
new source or dependencies live. There is no automatic rollback or automated
HTTP smoke check. Workflow concurrency does not coordinate manual SSH deploys.

The CI-success event triggers deployment, but `git pull` takes current main;
it does not pin the triggering run's SHA. This is preserved apps behavior,
not a claim that the deployed revision is always the exact tested revision.
Changes to this behavior require a separately agreed cross-repo improvement.

For host-specific OPcache revalidation or optional reload-hook setup, follow
the [deploy script setup walkthrough](../cli/deploy.sh). The end-to-end check
above verifies the expected visible change at the production HTTPS URL.
