#!/usr/bin/env bash
#
# Deploy winglib.
#
# Run it as the checkout owner: that user is also the php-fpm pool user
# (pool winglib.conf, "user = sergio"), so it can write storage/ and
# bootstrap/cache without sudo. Only the fpm reload needs sudo (NOPASSWD).
#
# The CLI must be php8.5; bare "php" is 8.2 on this host.
#
set -euo pipefail
cd "$(dirname "$(readlink -f "$0")")"

PHP=/usr/bin/php8.5

# bring the app back up whatever happens, so a failed step cannot leave the
# site in maintenance mode
trap 'echo "== deploy finished, bringing the app up =="; $PHP artisan up || echo "WARNING: artisan up failed, the site is still down" >&2' EXIT

$PHP artisan down || true

git pull --ff-only

# uncomment when composer.json or composer.lock changed in the pulled range.
# Composer runs under php8.5: the lock carries Symfony 8, which refuses to
# install on the host's default 8.2 CLI. Composer 2 is the only supported
# version here, and --no-scripts keeps the post-install hooks from regenerating
# _ide_helper.php inside the checkout, so package:discover runs as its own step.
#/usr/bin/php8.5 /usr/bin/composer install --no-dev --no-interaction --no-scripts --optimize-autoloader
#$PHP artisan package:discover

# config:cache is part of optimize, and it is safe: config/services.php builds
# the OAuth redirect URIs from config('app.url'), which the environment supplies
# to the CLI too, rather than from $_SERVER[SERVER_NAME].
$PHP artisan optimize

# uncomment when migrations are pending (check with: php8.5 artisan migrate:status)
#$PHP artisan migrate --force

sudo systemctl reload php8.5-fpm
