# winglib status

Host: `host.bruder.com.br` (Hetzner `ubuntu-4gb-nbg1-1`), user `sergio`.

## Remotes and branches

| remote | url | notes |
|---|---|---|
| `origin` | `git@github.com:sdbruder/winglib.git` | the only remote; GitHub is authoritative |

| branch | head | content |
|---|---|---|
| `master` | Laravel 13.40.0 on PHP 8.5 | the only deployed line since 2026-11-04; both sites deploy from it |
| `prod` | Laravel 5.1, tip `96dd1c6` | frozen line, kept as the rollback anchor for winglib.com until the window closes |
| `dev`, `hotfix` | Laravel 5.5-era | untouched |

`prod`'s tip is an ancestor of `master`; `master` additionally carries the whole
5.1 → 5.5 upgrade.

## Server layout

| path | site | branch | pool |
|---|---|---|---|
| `/var/www/winglib` | winglib.com (TLS) | `master` | `php8.5-fpm-winglib.sock` (user `sergio`) |
| `/var/www/beta.winglib` | beta.winglib.com (TLS) | `master` | `php8.5-fpm-winglib.sock` |

Both checkouts are owned by `sergio`, which is also the pool user, so PHP writes
`storage/` and `bootstrap/cache/` without sudo. Only the fpm reload needs sudo
(`NOPASSWD: ALL`). Vhosts live in `/etc/nginx/sites-available/`; the `.bak-*` files
next to them are the pre-change copies.

## Deploy

`deploy.sh` in the checkout, as `sergio`, from `master` (both sites now):
`artisan down`, `git pull --ff-only`, `rm -f bootstrap/cache/*.php`,
`artisan optimize`, `systemctl reload php8.5-fpm`, and an `EXIT` trap that brings
the app back up. The cache drop is not optional: a checkout that came from an
older Laravel keeps `bootstrap/cache/packages.php` and `services.php` for the old
provider list, the repository ignores both, and the first boot of the new code
died with `Class "Barryvdh\Debugbar\ServiceProvider" not found` until they went.

Dependencies, only when `composer.json`/`composer.lock` change:

```
/usr/bin/php8.5 /usr/bin/composer install --no-dev --no-interaction --no-scripts --optimize-autoloader
```

Composer runs under php8.5 because the lock carries Symfony 8, which the host's
default 8.2 CLI refuses to install; `--no-scripts` keeps the post-install hooks
from regenerating `_ide_helper.php` inside the checkout, so `artisan
package:discover` runs as its own step.

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

## Server maintenance (added 2026-11-03)

| item | where | detail |
|---|---|---|
| database dumps | `mysql-backup.timer` (05:15 UTC, `Persistent=true`) → `mysql-backup.service` → `/home/sergio/backups/mysql-backup.sh` | dumps every database except `information_schema`/`performance_schema`, gzips into `/home/sergio/backups/tmp` (mode 600, newest two kept), uploads to the Minecraft Storage Box under `mysql/`, and prunes remote archives older than `SB_RETAIN_DAYS` (30). Runs as `root`, because `root@localhost` is the shared credential every app on this host uses. |
| log rotation | `/etc/logrotate.d/laravel-winglib` | daily, 14 rotations, `copytruncate`, `su sergio sergio`, covering both `storage/logs` directories. The 228 MB prod log was rotated and compressed by hand on 2026-11-03. |
| swap | `/swapfile` (2G), `/etc/fstab`, `vm.swappiness=10` | the host ran MariaDB and two FPM pools in 3.8 GB of RAM with no swap at all |
| HTTP/2 | `listen 443 ssl http2` in `winglib.conf` (two 443 blocks) and `beta.winglib.conf` | nginx 1.24 enables HTTP/2 through the `listen` directive; the separate `http2 on;` form needs 1.25.1 |
| certbot cron | removed from root's crontab | it duplicated `certbot.timer` (05:19 UTC) and used `systemctl restart nginx` where the timer's own hooks reload. Backup: `/home/sergio/backups/root-crontab.bak-20261103`. Only the two winglib certificates carry `post_hook = systemctl reload nginx`; `bruder.com.br` and both `qr-lapide.com.br` certificates still carry `systemctl restart nginx`, so renewing those still restarts nginx. |
| config backups | `/home/sergio/backups/winglib-config/` | six mode-600 files: the beta `.env` copies and beta git-config copy taken during the 2026-11-03 work, plus prod snapshots (`prod.env.bak-20261103`, and `prod.git-config.bak-20261103` with the removed `[remote "bitbucket"]` block re-added). No `.bak-*` file is left inside either checkout. |

`sudo mariadb-upgrade --force` ran on 2026-11-03: `mysql.proc` carried a stale
definition, so every routine operation failed with `ERROR 1728 … Cannot load from
mysql.proc. The table is probably corrupted` — creating a procedure, `SHOW PROCEDURE
STATUS` and `mysqldump --routines` all died. Routine reads, creates and dumps work
again since the repair; the pre-repair table files are in
`/home/sergio/backups/mysql-systemtable-backup-20261103/`. The event scheduler is
`OFF` on this server, so the dumps pass `--skip-events`; `SHOW EVENTS` and
`mysqldump --events` fail with `ERROR 1577` otherwise.

## Upgrade to Laravel 13.40.0 (2026-11-04)

`master` moved from Laravel 5.5 on PHP 7.4 to Laravel 13.40.0 on PHP 8.5 in one
commit per hop, each walked against the official upgrade guide of its major, and
each verified against a characterisation suite that was written on the 5.5
behaviour first (48 tests, 178 assertions):

| hop | version | PHP stage | what came with it |
|---|---|---|---|
| 6 | 6.20.45 | 7.4 | fakerphp, mockery 1.x, `laravel/ui`, Rector, the removed 5.x APIs |
| 7 | 7.30.7 | 7.4 | `Str` and `Arr` moves, the session config env keys |
| 8 | 8.83.29 | 7.4 | factory classes, `HasFactory`, the paginator's Tailwind default |
| 9 | 9.52.23 | 8.2 | phpunit 9, Swiftmailer to the Symfony mailer, Larastan and Pint |
| 10 | 10.50.4 | 8.2 | phpunit 10, the suite to Pest, `$middlewareAliases` |
| 11 | 11.57.1 | 8.2 | laravelcollective/html to spatie/laravel-html, debugbar 4 (the Fruitcake namespace), ide-helper 3, Larastan level 5 with a baseline |
| 12 | 12.71.0 | 8.5 | Carbon 3, Symfony 8 |
| 13 | 13.40.0 | 8.5 | Pest 4 and phpunit 12, `PreventRequestForgery`, model metadata as attributes |

`docker/compose.yaml` carries php7.4, php8.2 and php8.5 stages next to MariaDB
10.11, so every hop ran against the engine this host serves. The toolchain the
hops adopted: Pint over 76 files, Rector through rector-laravel's level sets,
Larastan at level 5 with a committed baseline, and the suite itself.

Beyond the hops, the rollout had to fix two things: `config/mail.php` still had
the 5.5 layout, which Laravel 9 and later no longer read (the default mailer is
`mail.default`, the transports live under `mail.mailers`), and prod's Mailgun
transport needed `symfony/mailgun-mailer` with `symfony/http-client`. Both `.env`
files now carry `MAIL_MAILER` (prod `mailgun`, beta `log`).

### Rollback

Until the window closes, the `prod` branch (`96dd1c6`, Laravel 5.1) and the
`php7.4-fpm-winglib` pool are both still in place. To go back on `winglib.com`:
`git checkout prod` in `/var/www/winglib`, reinstall the 5.1 vendor with
`/home/sergio/bin/composer1.phar install --no-dev --no-interaction --no-scripts`,
point the vhost back at `/run/php/php7.4-fpm-winglib.sock`, reload nginx, and run
`php7.4 artisan up`. No database change is needed in either direction.

### Findings left open

- `composer audit` reports two medium XSS advisories in `components/jquery` 2.x
  (fixed in 3.5.0). Rebuilding the bundle needs node, which this host does not
  have; the build output is committed instead.
- The Larastan baseline carries pre-existing findings, among them
  `Wing::prepPanelsArray()` being unused and a dead array comparison.
- `app/Http/Middleware/EncryptCookies.php` and `Authenticate.php` are empty shells
  from the 5.x layout that no guide in this upgrade touches.

## Known gaps

- Both sites run `master`; the `prod` branch and the `php7.4` pool stay in place
  until the rollback window closes (the upgrade section has the way back).
- Migrations: 4 applied, 0 pending on prod and on `winglibbeta`. The upgrade added
  none, so the cutover needed no schema change.
- Sessions: both sites write `storage/framework/sessions` now, which is Laravel's
  own path from 5.5 on. `SESSION_PATH` in prod's `.env` is a 5.1 key that nothing
  reads any more. The cutover therefore logged everyone out once.
- Environments: beta runs `APP_ENV=staging`, `APP_DEBUG=false`; prod runs
  `APP_ENV=prod`, `APP_DEBUG=false`. Debugbar follows `app.debug`, so it is off on
  both.
- Cookies: `config/app.php` and `config/session.php` on both branches read
  `APP_URL`, `SESSION_COOKIE`, `SESSION_DOMAIN` and `SESSION_SECURE_COOKIE` from the
  environment, with the previous values as defaults, so a site that sets none of
  them behaves exactly as before. Beta sets all four (`https://beta.winglib.com`,
  `beta_laravel_session`, `beta.winglib.com`, `true`); prod sets `APP_URL` and
  `SESSION_SECURE_COOKIE=true` and keeps `laravel_session` on `winglib.com`. Before
  this, both sites wrote the same `laravel_session` cookie for the parent domain, so
  a visit to one invalidated the other's session.
- Prod's `XSRF-TOKEN` cookie still lacks `Secure`: Laravel 5.1 hard-codes that in
  `VerifyCsrfToken`, while 5.5 takes it from `session.secure`. Only the session
  cookie is affected by the setting.
- `_ide_helper.php` on prod carries one uncommitted generated-date line; two
  untracked verification files (`public/ads.txt`,
  `public/google7130d95414ed0aad.html`) match the blobs in `master` exactly.
- Social login on beta: `config/services.php` on `master` now builds each callback
  from `config('app.url')`, giving `https://beta.winglib.com/auth/<driver>/callback`.
  Facebook and Google accept it (`/auth/facebook` and `/auth/google` answer `302` to
  the provider), Twitter still answers `403 Callback URL not approved` because that
  URL is not in the Twitter app's callback list — that list lives in the provider's
  dashboard, not on this host.
- The `bitbucket` remote is gone from the local repo and from both server checkouts;
  `origin` (GitHub) is the only remote. The old URL survives only inside the backup
  copies under `/home/sergio/backups/winglib-config/`.
