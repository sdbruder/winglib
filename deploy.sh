#!/usr/bin/env bash
#
# Deploy winglib.
#
# Run it as the checkout owner: that user is also the php-fpm pool user
# (pool winglib.conf, "user = sergio"), so it can write storage/ and
# bootstrap/cache without sudo. Only the fpm reload needs sudo (NOPASSWD).
#
# The PHP version comes from the Nginx vhost that serves this checkout's
# public/ directory. The CLI, Composer and the reloaded FPM pool therefore
# match the web runtime: production stays on 8.5 and beta runs 8.6 without
# editing this script. The host's bare "php" is 8.2 and must not be used.
#
set -euo pipefail
cd "$(dirname "$(readlink -f "$0")")"

APP_ROOT="$(pwd)"

# Find the vhost whose document root is this checkout's public/ directory.
vhost=""
for file in /etc/nginx/sites-enabled/*.conf; do
    [ -r "$file" ] || continue
    if awk -v target="$APP_ROOT/public" '
        $1 == "root" {
            sub(/;$/, "", $2)
            if ($2 == target || $2 == target "/") { found = 1 }
        }
        END { exit !found }
    ' "$file"; then
        vhost="$file"
        break
    fi
done
if [ -z "$vhost" ]; then
    echo "ERROR: no Nginx vhost serves $APP_ROOT/public" >&2
    exit 1
fi

# The fastcgi_pass socket names the FPM pool, and the pool name carries the
# PHP version: /run/php/php8.6-fpm-winglib.sock -> 8.6.
socket="$(grep -oE 'fastcgi_pass[[:space:]]+unix:[^;]+' "$vhost" | head -n 1 || true)"
php_version="$(printf '%s\n' "$socket" | sed -nE 's#.*/php([0-9]+\.[0-9]+)-fpm.*#\1#p')"
if [ -z "$php_version" ]; then
    echo "ERROR: cannot read the PHP version from $vhost" >&2
    exit 1
fi

PHP="/usr/bin/php$php_version"
FPM="php$php_version-fpm"
if [ ! -x "$PHP" ]; then
    echo "ERROR: $PHP not found" >&2
    exit 1
fi

echo "== deploying $APP_ROOT on PHP $php_version =="

# bring the app back up whatever happens, so a failed step cannot leave the
# site in maintenance mode
trap 'echo "== deploy finished, bringing the app up =="; $PHP artisan up || echo "WARNING: artisan up failed, the site is still down" >&2' EXIT

$PHP artisan down || true

git pull --ff-only

# A checkout that came from an older Laravel carries bootstrap/cache/packages.php
# and services.php for the old service provider list. The repository ignores
# them, so a pull never replaces them, and the new code would try to load the
# old providers. Drop them; package:discover and optimize rebuild them.
rm -f bootstrap/cache/*.php

# uncomment when composer.json or composer.lock changed in the pulled range.
# Composer runs under the same PHP as the pool: the lock carries Symfony 8,
# which refuses to install on the host's default 8.2 CLI. Composer 2 is the
# only supported version here, and --no-scripts keeps the post-install hooks
# from regenerating _ide_helper.php inside the checkout, so package:discover
# runs as its own step.
#$PHP /usr/bin/composer install --no-dev --no-interaction --no-scripts --optimize-autoloader
#$PHP artisan package:discover

# config:cache is part of optimize, and it is safe: config/services.php builds
# the OAuth redirect URIs from config('app.url'), which the environment supplies
# to the CLI too, rather than from $_SERVER[SERVER_NAME].
$PHP artisan optimize

# uncomment when migrations are pending (check with: $PHP artisan migrate:status)
#$PHP artisan migrate --force

sudo systemctl reload "$FPM"
