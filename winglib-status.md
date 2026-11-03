# winglib status

Host: `host.bruder.com.br` (Hetzner `ubuntu-4gb-nbg1-1`), user `sergio`.

## Remotes and branches

| remote | url | notes |
|---|---|---|
| `origin` | `git@github.com:sdbruder/winglib.git` | primary since 2026-11-03 |
| `bitbucket` | `ssh://git@ssh.bitbucket.org/sdbruder/winglib.git` | fallback, kept for now |

Bitbucket moved SSH to `ssh.bitbucket.org` on 2026-11-12; the old host
`bitbucket.org` is no longer in any `known_hosts`, so pulls from it fail.

| branch | head | content |
|---|---|---|
| `master` | Laravel 5.5 | active line; `beta.winglib.com` deploys from it |
| `prod` | Laravel 5.1, tip `7f72abd` (2018) | frozen line; `winglib.com` deploys from it |
| `dev`, `hotfix` | Laravel 5.5-era | untouched |

`prod`'s tip is an ancestor of `master`; `master` additionally carries the whole
5.1 → 5.5 upgrade. Bitbucket trails both branches by a few commits.

## Server layout

| path | site | branch | pool |
|---|---|---|---|
| `/var/www/winglib` | winglib.com (TLS) | `prod` | `php7.4-fpm-winglib.sock` (user `sergio`) |
| `/var/www/beta.winglib` | beta.winglib.com (port 80 only) | `master` | `php7.4-fpm-winglib.sock` |

Both checkouts are owned by `sergio`, which is also the pool user, so PHP writes
`storage/` and `bootstrap/cache/` without sudo. Only the fpm reload needs sudo
(`NOPASSWD: ALL`). Vhosts live in `/etc/nginx/sites-available/`; the `.bak-*` files
next to them are the pre-change copies.

## Deploy

`deploy.sh` in the checkout, as `sergio`, from the branch that site deploys:
`artisan down`, `git pull --ff-only`, `clear-compiled`, `optimize`,
`config:clear`, `route:clear`, `view:clear`, `cache:clear`,
`systemctl reload php7.4-fpm`, and an `EXIT` trap that brings the app back up.
`config:cache` is deliberately absent: on the 5.1 line `config/services.php`
builds the OAuth redirect URIs from `$_SERVER[SERVER_NAME]`, which is unset in CLI
and would be cached as an empty host.

Dependencies, only when `composer.json`/`composer.lock` change:

```
/home/sergio/bin/composer1.phar install --no-dev --no-interaction --no-scripts --optimize-autoloader
```

Composer 1 is required: Composer 2 writes `vendor/composer/installed.json` in a
format Laravel 5.5 cannot read (`PackageManifest`: `Undefined index: name`).
`--no-scripts` stops the post-install hooks from regenerating `_ide_helper.php`
inside the checkout.

## TLS

Both sites terminate TLS in nginx with Let's Encrypt certificates and renew through
the webroot authenticator, so the HTTP-01 challenge path on port 80 stays outside
the redirect to HTTPS:

| site | certificate | renews | note |
|---|---|---|---|
| winglib.com, www | `winglib.com` (ECDSA) | 2027-02-01 | vhost redirects www to the apex |
| beta.winglib.com | `beta.winglib.com` (ECDSA) | 2027-02-01 | added 2026-11-03 |

Both renewal configs carry `post_hook = systemctl reload nginx`; `certbot certonly`
does not add that hook on its own, so a renewed certificate would sit unused until
the next manual reload. Beta's renewal was proven by a forced renewal on
2026-11-03: the issued serial changed and nginx served the new serial right away.
`certbot renew --dry-run` cannot be used on this host, because it talks to
`acme-staging-v02.api.letsencrypt.org`, which is unreachable here (the production
endpoint answers in under a second).

## Known gaps

- **beta runs with `APP_ENV=local` and `APP_DEBUG=true`**, which shows debugbar and
  exception details to visitors.
- `winglib.com` still runs the 5.1 line while `master` holds the 5.5 upgrade;
  moving it is a deploy decision, and it changes the artisan steps.
- Migrations: 4 applied, 0 pending on prod. The beta database is `winglibbeta`
  (user `root` at `localhost`) and has not been migrated for the 5.5 line.
- Sessions: prod uses `SESSION_PATH=/var/lib/php/session` (created 2026-11-03 with
  mode `1733 root:root`, as the app expected); beta uses its own
  `storage/framework/sessions`, because Laravel 5.5's own session GC has to list
  the directory and `1733` forbids that to a non-owner.
- Session cookies: `config/session.php` now reads `SESSION_DOMAIN` and
  `SESSION_SECURE_COOKIE` from the environment, with the old values as defaults, so
  no behaviour changed for a site that sets neither. Beta sets
  `SESSION_DOMAIN=beta.winglib.com` and `SESSION_SECURE_COOKIE=true`: before that,
  beta and winglib.com both wrote a `domain=winglib.com` cookie, so visiting one
  site overwrote the other's session cookie. Prod still uses the shared
  `winglib.com` domain and no `Secure` flag.
- `_ide_helper.php` on prod carries one uncommitted generated-date line; two
  untracked verification files (`public/ads.txt`,
  `public/google7130d95414ed0aad.html`) match the blobs in `master` exactly.
