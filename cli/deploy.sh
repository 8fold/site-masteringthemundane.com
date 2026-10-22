#!/usr/bin/env bash
#
# Pull main, install lockfile-pinned production dependencies and validate.
# Setup and the owner-managed /public cutover: docs/deployment.md.
# Adapted from apps' cli/deploy.sh; no database or account provisioning here.

set -euo pipefail

cd "$(dirname "$0")/.."

step="startup"
trap 'echo "deploy.sh: FAILED during: ${step} (exit $?)" >&2' ERR

step="git pull (fast-forward only)"
echo "==> ${step}"
git pull --ff-only

step="composer install (lockfile-pinned, production autoloader)"
echo "==> ${step}"
composer install --no-dev --optimize-autoloader --no-interaction

step="post-deploy validation: composer validate"
echo "==> ${step}"
composer validate --no-check-publish

step="post-deploy validation: php -l on entry points"
echo "==> ${step}"
find public -name index.php -print0 | xargs -0 -n1 php -l > /dev/null

# Server-specific post-deploy steps (OPcache/PHP-FPM reload, cache
# clearing) live in a gitignored hook — host details do not belong in
# the repo. A failing hook fails the deploy like any other step.
step="server post-deploy hook (config/deploy-server.sh)"
echo "==> ${step}"
hook="config/deploy-server.sh"
if [ -f "$hook" ]; then
    bash "$hook"
else
    echo "skipped: no ${hook}; OPcache may serve stale code until"
    echo "revalidation (see docs/deployment.md)."
fi

trap - ERR
echo "deploy.sh: deployed $(git rev-parse --short HEAD) successfully."
