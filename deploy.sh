#!/usr/bin/env bash
#
# Deploy winglib.
#
# Run it as the checkout owner: that user is also the php-fpm pool user
# (pool winglib.conf, "user = sergio"), so it can write storage/ and
# bootstrap/cache without sudo. Only the fpm reload needs sudo (NOPASSWD).
#
# The CLI must be php7.4. Bare "php" is 8.2 on this host and dies on the
# Laravel 5.1 compiled cache:
#   Cannot declare class Symfony\Component\HttpFoundation\Request
#
set -euo pipefail
cd "$(dirname "$(readlink -f "$0")")"

PHP=/usr/bin/php7.4

# bring the app back up whatever happens, so a failed step cannot leave the
# site in maintenance mode
trap 'echo "== deploy finished, bringing the app up =="; $PHP artisan up || echo "WARNING: artisan up failed, the site is still down" >&2' EXIT

$PHP artisan down || true

git pull --ff-only

# uncomment when composer.json or composer.lock changed in the pulled range.
# Use Composer 1: these are old Laravel versions, and Composer 2 rewrites
# vendor/composer/installed.json into a format Laravel 5.5 cannot read
# (PackageManifest: "Undefined index: name"). --no-scripts keeps the
# post-install hooks from regenerating _ide_helper.php inside the checkout.
#/home/sergio/bin/composer1.phar install --no-dev --no-interaction --no-scripts --optimize-autoloader

# bootstrap/cache/compiled.php is a Laravel 5.1 class cache: drop it before the
# new code runs, then rebuild it together with services.json.
$PHP artisan clear-compiled
$PHP artisan optimize

# Never config:cache here. config/services.php builds the OAuth redirect URIs
# from $_SERVER[SERVER_NAME], which is unset in CLI, so the cached value would
# hold an empty host. config:clear drops any cache an earlier deploy left.
$PHP artisan config:clear
$PHP artisan route:clear
$PHP artisan view:clear
$PHP artisan cache:clear

# uncomment when migrations are pending (check with: php7.4 artisan migrate:status)
#$PHP artisan migrate --force

sudo systemctl reload php7.4-fpm
