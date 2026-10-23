#!/usr/bin/env bash
#
# The deploy path (see docs/deployment.md): pull main, install the
# lockfile-pinned production dependencies and regenerate the autoloader,
# then validate the result. Stops at the first failing step and names it;
# a non-zero exit means the deployment did NOT complete and the output
# says which step failed. This is an in-place deployment, not an atomic
# release switch or automatic rollback.
#
# Assumes git, PHP 8.5+, and composer on PATH (DreamHost VPS over SSH), run
# from anywhere -- the script cds to the repository root itself.
# Adapted from apps' cli/deploy.sh; .com has no database migrations,
# practitioner provisioning or secret environment-file setup.
#
# First-time setup for the Deploy GitHub Actions workflow. Follow the
# owner-managed webhook and /public cutover in docs/deployment.md first:
# do not leave the old webhook deploying into the same checkout.
# The commands below are setup instructions, not actions this script runs.
#
# Paste hygiene: copy ONE command line at a time, exactly as written --
# never the words around it. If bash answers 'SomeWord: command not
# found', a fragment of an English sentence was pasted as a command;
# re-copy just the command.
#
#   DreamHost -- recommended: a DEDICATED deploy user (panel -> VPS ->
#     Manage Users -> Add), not your everyday SFTP/SSH user. Scope its
#     access to this checkout so a leaked deploy key has limited reach.
#     Step 1 runs as that user; Apache's web directory must point at
#     /home/<deployuser>/mtm-com/public (Manage Websites -> the domain).
#     Enable HTTPS and set the domain's PHP version to 8.5 in the panel;
#     web PHP and shell PHP are configured separately.
#     These are owner-managed changes: preserve the working checkout and
#     hosting configuration before replacing the production deployment.
#
#     1. Clone, or verify the existing production checkout. If the
#        repository requires authentication, the server needs its OWN
#        GitHub credential before git can clone or pull. On the server:
#          ssh-keygen -t ed25519 -f ~/.ssh/id_ed25519
#          cat ~/.ssh/id_ed25519.pub
#        Do not overwrite an existing key. Add the public output at
#        GitHub -> repo -> Settings -> Deploy keys (read-only is enough).
#        Then, for a new checkout:
#          git clone git@github.com:8fold/site-masteringthemundane.com.git mtm-com
#        For a public repo, HTTPS needs no read credential:
#          git clone https://github.com/8fold/site-masteringthemundane.com.git mtm-com
#        CHECK: cd ~/mtm-com && pwd && git branch --show-current
#        -> prints the checkout path (this is DEPLOY_PATH) and "main".
#        'Repository not found' = check the server's GitHub access.
#        'Destination path already exists' = inspect the existing path;
#        do not delete a working checkout or its server-specific files.
#        No apps database, mail, Stripe or account setup is needed here.
#     2. Shell tools, the non-interactive kind. git comes preinstalled;
#        PHP 8.5 for the shell and a global composer install follow the
#        DreamHost guides. PATH configuration must also work for the
#        non-interactive SSH session GitHub Actions opens. If ~/.bashrc
#        returns early at '[ -z "$PS1" ] && return', required PATH lines
#        belong ABOVE that line; interactive-only aliases are not enough.
#        CHECK (from your Mac, not while SSHed in):
#          ssh deployuser@your-server 'php -v && composer -V && git --version'
#        All three must print the expected versions. Verify ext-dom for
#        production PHP too; Composer enforces locked platform requirements.
#     3. The deploy key -- the credential the GitHub runner uses to
#        authenticate to the server. Commands run on YOUR MAC:
#          a. ssh-keygen -t ed25519 -f ~/.ssh/mtm-com-deploy
#             Use an unused filename and an empty passphrase: the runner
#             cannot type one. The fingerprint and randomart indicate
#             creation; the files are ~/.ssh/mtm-com-deploy (private) and
#             ~/.ssh/mtm-com-deploy.pub (public).
#          b. Install the public half on the server. If the deploy user
#             has a password you know:
#               ssh deployuser@your-server 'mkdir -p ~/.ssh && chmod 700 ~/.ssh'
#               cat ~/.ssh/mtm-com-deploy.pub | ssh deployuser@your-server 'cat >> ~/.ssh/authorized_keys && chmod 600 ~/.ssh/authorized_keys'
#             The password prompt asks for the SERVER deploy user's
#             password, not your Mac password. If you administer the
#             deploy user through a separate sudo-enabled SSH user:
#               cat ~/.ssh/mtm-com-deploy.pub | ssh your-main-user@your-server 'sudo -u deployuser bash -c "mkdir -p ~/.ssh && chmod 700 ~/.ssh && cat >> ~/.ssh/authorized_keys && chmod 600 ~/.ssh/authorized_keys"'
#             '>>' appends to the list of authorized public keys.
#          c. CHECK (from your Mac):
#               ssh -i ~/.ssh/mtm-com-deploy deployuser@your-server 'echo key works'
#             'key works' confirms the connection the runner will use.
#     4. Pin the server's host key so the workflow can refuse impostors.
#        A runner cannot answer a first-connect trust prompt. On YOUR MAC:
#          ssh-keyscan -t ed25519 your-server
#        Verify the fingerprint through an independent trusted channel
#        before trusting this output; ssh-keyscan alone proves no identity.
#        Save the verified host-key lines for DEPLOY_KNOWN_HOSTS below.
#
#   OPcache refresh -- the 'did the new code actually go live?' question.
#     PHP may serve OLD compiled code after a pull until cache revalidation.
#     Check opcache.validate_timestamps for the WEB PHP configuration;
#     CLI phpinfo() alone does not prove the web SAPI uses the same setting.
#     If enabled, verify its revalidation interval and confirm the changed
#     page is served after deployment. If disabled, two owner-managed options:
#       a. Manual: reload/restart the web PHP service through the hosting
#          controls after deployment. The workflow cannot click the panel.
#       b. Automatic: create gitignored config/deploy-server.sh so this
#          script gracefully reloads the actual PHP-FPM service. On SERVER:
#            i. Find the service name, if the host uses systemd/PHP-FPM:
#                 systemctl list-units --type=service | grep -i fpm
#            ii. nano ~/mtm-com/config/deploy-server.sh
#                Use the actual service name; example contents:
#                  #!/usr/bin/env bash
#                  sudo -n systemctl reload php8.5-fpm
#                Then:
#                  chmod +x ~/mtm-com/config/deploy-server.sh
#            iii. An administrator can allow ONLY that service reload,
#                 password-free (the workflow cannot type a password):
#                   sudo visudo -f /etc/sudoers.d/mtm-deploy
#                 Example, with your actual names and executable path:
#                   deployuser ALL=(root) NOPASSWD: /usr/bin/systemctl reload php8.5-fpm
#                 Use visudo so syntax is checked before saving.
#            iv. CHECK: ~/mtm-com/config/deploy-server.sh && echo "hook works"
#          CLI opcache_reset() does NOT clear the cache used by web
#          requests. No systemctl/PHP-FPM on the plan? Ask hosting support
#          for the appropriate web PHP refresh; do not assume this example.
#     With no hook file, this script still succeeds but warns.
#
#   GitHub (repo -> Settings -> Secrets and variables -> Actions).
#     Configure these only after the server checks above pass:
#     1. Secret DEPLOY_SSH_KEY: on your Mac,
#        `cat ~/.ssh/mtm-com-deploy` -- copy the WHOLE private key including
#        BEGIN/END OPENSSH PRIVATE KEY lines directly into the Actions
#        secret. Never paste it into chat, logs or a repository file.
#     2. Secret DEPLOY_KNOWN_HOSTS: verified host-key output from step 4,
#        pasted as-is.
#     3. Variables (the Variables tab): DEPLOY_USER (deploy username),
#        DEPLOY_HOST (server name), DEPLOY_PATH (repository root from
#        step 1, e.g. /home/deployuser/mtm-com, NOT its public directory).
#     4. Rehearse on the prepared server checkout: ./cli/deploy.sh should
#        install the locked production dependencies, validate, then end
#        with the success line. This changes the checkout/dependencies;
#        coordinate the rehearsal with the owner and the webroot cutover.
#        workflow_run requires the workflow file on the default branch.
#        After activation, watch subsequent main deployments in Actions;
#        a failed deploy names the failing step in this script's output.
#        Script success does not verify HTTP: manually check the live
#        pages, static assets and cache headers per docs/deployment.md.
#

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
