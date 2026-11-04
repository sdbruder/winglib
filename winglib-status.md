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

The window closes on **2026-11-11**: after that date the `prod` branch, the
`php7.4-fpm-winglib` pool and the `prod.env.bak-20261103-upgrade` /
`winglib.conf.bak-20261103-upgrade` backups lose their rollback role and can be
removed.

### Findings left open

- `composer audit` reports two medium XSS advisories in `components/jquery` 2.x
  (fixed in 3.5.0). Rebuilding the bundle needs node, which this host does not
  have; the build output is committed instead.
- The Larastan baseline carries pre-existing findings, among them
  `Wing::prepPanelsArray()` being unused and a dead array comparison.
- `app/Http/Middleware/EncryptCookies.php` and `Authenticate.php` are empty shells
  from the 5.x layout that no guide in this upgrade touches.

## Wing images

`public/wingimg` is a symlink to `storage/app/wingimg`, so an image URL is only as good as the file
behind it. Beta carried the rows but none of the files — its two wings are copies of prod rows — and
served 404 for both until prod's tree was copied over (3657 files, 52 MB). Two commands now cover
the general case, both of them additive and neither touching a file that is still there:

| command | what it does |
|---|---|
| `php8.5 artisan wings:images [--missing] [--paths]` | reports what the database references against `storage/app/wingimg`, and which of those are not on disk |
| `php8.5 artisan wings:draw {id} [--missing] [--all] [--force]` | redraws the image from the wing's own measurements (panels, cgpos, unitsystem, drawvalues) the way `public/js/winglib.js` draws it |

`wings:draw` is a GD port of that canvas code, font included (`resources/fonts/DejaVuSans.ttf`); GD
arrives with the `php8.5-gd` package here and with the container image. It was exercised on beta by
deleting one wing's PNG and drawing it back — 940x400 RGBA, then restored.

Every image is now drawn in the bolder style (3 px outlines, 4 px ticks, ~11.5 px labels, a
`#f4f6ff` panel fill, and a margin that is a share of the canvas rather than a fixed 30 px):
thumbnails at 180 and 282 px for the home panels and the library listing, full size at 940 px for
the wing page. Tallies: beta 2 full size and 2+2 thumbnails, prod 3127 full size and 3127 at each
thumbnail width. One prod wing is skipped and reported — `losange-6` has a panel with a span and no
chords, so there is no MAC to find (the canvas would have landed on `NaN` there).

The pre-redraw files are at `/home/sergio/backups/wingimg-{winglib,beta.winglib}-2015-20261103`
(76 and 52 MB). To restore: `rsync -a --delete <that directory>/ storage/app/wingimg/`.

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

## Wing thumbnails on every write (2026-11-04)

The 180 and 282 px thumbnails are now written by the model, not only by `wing:draw`.
A `saved` hook on `Wing` calls `refreshImageVariants()`, so a wing that is created,
edited or forked gets its thumbnails at once, drawn from the same data as the full
size image. `wing:draw` shares that code and still regenerates whole batches.

- `WingDrawing` gained `saveImage()` and `saveImageVariant()`; the command and the
  hook both call them.
- The hook reads `unitsystem`, `drawvalues`, `cgpos` and `rootchord` from the raw
  attribute array. A wing that was just saved from a form which did not post those
  fields carries no such attribute on the in-memory model, and a property read on a
  missing attribute raises a warning inside the save that triggered the hook. The
  first version used property reads, the render threw `Undefined array key "cgpos"`
  into the hook's own `catch (Throwable)`, and the thumbnails were silently never
  written.
- `WingImageVariantsTest` covers create, fork, edit and the full size redraw. The
  drawing test suite and the page tests are unchanged.

### Deployed 2026-11-04

`deploy.sh` on both sites from `master`, commit `033fd9d`. Proven live by deleting
a thumbnail and saving the wing that owns it:

| site | `image_file` | deleted | after the save |
|---|---|---|---|
| beta | `/wingimg/52/56595406c49fc.png` | 180 px | 698 B, back |
| prod | `/wingimg/57/565a3b8c3c08d.png` | 180 and 282 px | 698 B and 1044 B, back |

The home page answered `200` before and after on both sites. The delete and the
save ran through `php8.5 artisan tinker`; the server's bare `php` is 8.2 and fails
the Composer platform check, so hand-run commands on this host must say `php8.5`.

## Code quality after the upgrade (2026-11-04)

The toolchain that carried the nine hops now runs on demand and on every push.
`composer check` runs Pint, PHPStan, composer-unused and the Pest suite, and
`.github/workflows/ci.yml` runs it on PHP 8.5 against MariaDB 10.11.

- PHPStan moved from level 5 to level 6, and the analysed paths grew from `app`
  to `app`, `config`, `database` and `routes`. The 60 level 6 findings were fixed
  with docblocks only, with no behaviour change; 7 baseline entries went stale and
  were deleted, and none were added.
- New dev tools: `laravel/boost` (the MCP server), `phpstan/phpstan-deprecation-rules`,
  `spaze/phpstan-disallowed-calls` (its dangerous and execution sets, plus `env()`
  outside `config/`), and `icanhazstring/composer-unused`. `composer audit` reports
  in CI without gating, for the reason below.
- `.mcp.json` registers `php artisan boost:mcp`, so an MCP client can read this
  app's version-specific docs, schema, log and last error.
- PHPStan runs serially (`parallel.maximumNumberOfProcesses: 1`): in the project
  container its workers die with exit code 255, which turns a run into
  "Result is incomplete" with zero reported errors.
- `imagedestroy()` is deprecated in PHP 8.5; its one call site is gone.
- `composer-unused` needed six justified ignores to be usable: `laravel-ide-helper`,
  `gravatar` and `flash` are reached through Laravel conventions, `spatie/laravel-html`
  through Blade, `components/jquery` through `gulpfile.js` (outside the scan), and
  `fruitcake/laravel-debugbar` is an artefact of the lock file, whose `barryvdh`
  entry still carries the old repository URL.

### Open: jQuery 2.1.4 reaches every browser

`composer audit` reports two medium XSS advisories (CVE-2020-11022, CVE-2020-11023)
against `components/jquery` 2.2.4, and `roave/security-advisories` refuses to install
while that package is present (`conflicts with components/jquery >=1.0.3,<3.5`).
The live exposure is larger than the advisory: every page loads `public/js/all.js`
(`resources/views/layout.blade.php:212`) and that file bundles jQuery 2.1.4.
`gulpfile.js` is the only consumer of the Composer package - it copies
`vendor/components/jquery/jquery.js` into the bundle - and that Elixir/Gulp 3 build
does not run on a current Node.

### Resolved: jQuery 3.7.1 and Bootstrap 3.4.1 (2026-11-04)

Decided with the user, and the smoke test earned its keep:

- `components/jquery` 2.2.4 -> `^3.5` (resolves 3.7.1). `composer audit` is now clean
  and `roave/security-advisories` installs, which it could not before because it
  `conflicts with components/jquery >=1.0.3,<3.5`. The audit step in CI is a gate now,
  not a report.
- `public/js/jquery-3.7.1.min.js` is vendored from the same Composer package
  (`vendor/components/jquery/jquery.min.js`) and is loaded before the bundle.
- The jQuery 2.1.4 block left `public/js/all.js`, which fell from 439 KB to 191 KB.
- **The first smoke test failed**: Bootstrap 3.3.6 refuses jQuery >= 3 ("Bootstrap's
  JavaScript requires jQuery version 1.9.1 or higher, but lower than version 3"), so
  none of its plugins attached. Bootstrap's block inside the bundle was replaced with
  3.4.1, fetched from npm with the registry integrity checked against the tarball's own
  sha512. 3.4.1 is the last 3.x and also carries the CVE-2019-8331 tooltip and popover
  XSS fix.
- Verified in a browser with caching disabled on `/`, `/wings` and `/auth/login`:
  `jQuery.fn.jquery` is 3.7.1, `jQuery.fn.modal.Constructor.VERSION` is 3.4.1, and
  modal, tooltip, dropdown, collapse, tab and popover are all attached; both assets
  answer 200, and no Bootstrap or jQuery error appears.
- Both edits are surgical edits to the committed bundle, because neither `gulpfile.js`
  nor its Elixir/Gulp 3 build can run today. The previous file stays in git history.

Deployed to both sites at `0443a4e`, each after
`composer install --no-dev --no-scripts --optimize-autoloader`. Verified in a browser
with caching disabled on `/`, `/wings`, one wing page and `/auth/login` on both sites:
jQuery 3.7.1, Bootstrap 3.4.1, six of six plugins attached, no broken image, both
assets `200`, and no Bootstrap or jQuery error. Prod served `/js/all.js` at 198018
bytes and `/js/jquery-3.7.1.min.js` at 87533 bytes - the sizes built locally - and the
wing thumbnails written by the earlier fix are still in place.

### Mutation testing (2026-11-04)

`composer mutate` runs Pest's mutation testing. It needs a coverage driver, so the
php >= 8 stages of `docker/Dockerfile` build and enable pcov.

Scope note: `--covered-only` yields **zero** mutations in this suite. It keys off
`covers()`/`mutates()` declarations in the tests, and there are none, so the scope that
works is `--everything`.

Coverage is 77.6 % over `app/`: `Support/WingDrawing` 69.3 %, `Wing` 93.0 %,
`HomeController` and `WingRequest` 100 %, while `UsersController`, `Authenticate` and
`BroadcastServiceProvider` sit at 0 %. A mutant costs about 0.7 s on a thin class and
about 6 s on the drawing class, because Pest re-runs the tests covering it.

The first full run was time-boxed to 45 minutes and reached 8 files, deciding 441
mutants: **270 killed, 171 survived - 61.2 %**. The bulk was `Wing` (382 mutants),
`WingDrawing` had only started (165), `User` 28, the providers 14. The survivors
clustered in `WingDrawing::readPanels()` lines 164-166, the reads that fall back to
defaults, because every other test states `unitsystem`, `drawvalues`, `cgpos` and
`rootchord` outright and nothing exercised a wing that lacks them - the exact case the
create and fork paths render with.

`tests/Unit/WingDrawingTest.php` now covers it: a wing whose attributes are unset must
draw the same image as one that states the fallback values, and the render must carry
the PNG signature. Checked by hand - with the survived `?? false` to `?? true` mutation
in place the new test fails, and with the real code it passes.

To finish the run: `--parallel` would need a database per process (this suite shares
`winglib_test`, and `RefreshDatabase` runs `migrate:fresh` in each worker), so either
set that up or accept repeated time-boxed serial runs. Whether the mutation cache
resumes between runs is not yet verified.

## Mutation testing, finished: 64.4 % -> 81.2 %

`--parallel` needed no per-process database after all: Pest's Laravel plugin appends the
worker's `TEST_TOKEN` to the configured name and creates `winglib_test_test_1..8` itself,
so the whole app is measured in one 63 s run (1539 mutants) instead of several 45 minute
serial ones. Serial is roughly 300 times slower, and the per-mutation detail it prints is
what a parallel run does not: parallel reports only the uncovered mutants and the slowest
tests.

Whole app, parallel, 8 processes:

| run | uncovered | tested | score |
|---|---|---|---|
| baseline | 548 | 991 | 64.39 % |
| after | 290 | 1249 | **81.16 %** |

The 548 uncovered clustered in `WingDrawing` (318): `panel()`'s measure block (lines
320-379) and the whole of `centerOfGravity()` (406-427) were never executed, because
`Wing::saved` only draws when the wing already has an image and the factories set none.
Four tests in `tests/Unit/WingDrawingTest.php` now cover those paths and assert their
geometry: the cg ball's ink box and the MAC line, the measure lines with their ticks and
the labels' ink bands, the arc's start, and - the decisive part - a fixture 440 wide by
300 tall where `min(880/880, 340/300) = 1` makes one model unit one pixel. That is the
only way a one unit change in `GAP`, `TICK`, `LINE`, `CHORD_GAP` or `CG_GAP` can be seen
at all; at the other fixtures' zoom of 0.708 such a change is sub pixel and every
assertion misses it.

Hand-checked kills, through `/tmp/verify-kills.sh` (8 mutants applied and reverted: the
five constants, the arc radius ternary, the cg diameter `max -> min`, `num()`'s `rtrim`):
2 of 8 were killed before these tests and 7 of 8 after. `num()`'s formatting mutant still
survives, because a label is observable only as pixels and both `600` and `600.00` leave
ink in the band.

What is left uncovered, and why:

- `Wing` 97 - the save hooks, `duplicateImage`, `saveImage`, `refreshImageVariants` and
  `imageVariant`; the next cluster worth a test.
- `WingDrawing` 60 - surviving arithmetic in `readPanels()` and `withCenterOfGravity()`.
- `WingDraw` 29 and `Inspire` 7 - command internals. `Inspire` is Laravel's demo command
  with no caller, so deleting it is cheaper than testing it.
- `Kernel` 17, `Handler` 12, `Authenticate` 6, `BroadcastServiceProvider` 5 - framework
  glue, deliberately not tested.

Two findings the drawing tests surfaced, both checked with the owner:

1. `app/Http/Controllers/UsersController.php`'s seven empty resource methods
   (`index`, `create`, `store`, `show`, `edit`, `update`, `destroy`) and its unused
   `Illuminate\Http\Request` import, which nothing routed to - deleted, and
   `app/Console/Commands/Inspire.php` with them: Laravel's demo command, no caller, 7
   uncovered mutants. `phpstan-baseline.neon` shrank by the 7 `return.missing` entries
   those methods held, in the same change, which is what `ignore.unmatched` demands.
2. Checking `WingDrawing::text()` for the gap a column shows at x 470 rows
   64-78 found no bug. That gap is not the label strip erasing the line under it: with
   alpha blending on, `imagecopy` composites the strip correctly, and the gap is the cg
   ball painted over the left edge - it is 13 px across, centred at (470, 71). The sparse
   rows lower down are the rotated glyph strokes themselves. Measured by replacing the
   composite with a hand written per pixel loop: the rendered columns came out identical,
   so the original stands.

Reproduce: `~/.omp/agent/bin/zj-run.sh mutate2 . /tmp/mut2.sh`, or in the container
`timeout 5400 php vendor/bin/pest --mutate --everything --parallel --processes=8 --profile`.

## The Wing round

`Wing` held 97 uncovered mutants. 71 of them are entries of the `#[Fillable]` and
`#[Appends]` attributes, 15 are `prepPanelsArray()`, 3 the widths in `IMAGE_VARIANTS`,
and the rest sit in the save hooks.

Two tests closed the last two groups: one mass assigns every field the create, edit and
fork forms post and asserts the model's attributes match, plus that `id`, `created_at`
and `slug_str` stay unfillable; the other names the two thumbnail files the views ask
for, because every existing variant test read `Wing::IMAGE_VARIANTS` and so moved with a
change to it.

The Wing round's own tests move little on their own - 290 uncovered to 283, 81.16 % to
81.53 %, and the 7 that left are `Inspire`'s, which was deleted. The 71 attribute
mutants stay in the uncovered bucket because Pest attributes coverage to *executed
lines*, and a declaration is never executed: no test can move them, whatever it asserts.
Hand-checked anyway, on the real file, by applying each mutation and reverting it:

| mutation | result |
|---|---|
| first `'unitsystem'` entry removed (the Fillable list) | KILLED - WingModelTest fails |
| `IMAGE_VARIANTS` 180 -> 181 | KILLED - the named-width test fails |
| first `'panel3angle'` entry removed (the Appends list) | KILLED - 8 WingModelTest and 3 WingImageVariantsTest cases fail |

So the behaviour behind those mutants is pinned even though the tool cannot score it.

With the forward-sweep fixture in, and `prepPanelsArray()` and its 15 mutants deleted,
the whole app re-measures as:

| run | uncovered | tested | score | suite |
|---|---|---|---|---|
| baseline | 548 | 991 | 64.39 % | 66 / 240 |
| after the drawing round | 290 | 1249 | 81.16 % | 70 / 293 |
| after the Wing round | 283 | 1249 | 81.53 % | 72 / 301 |
| after the forward sweep and the deletion | 231 | 1275 | 84.66 % | 73 / 305 |
| after the `wings:draw` round | **202** | **1304** | **86.59 %** | 77 / 317 |

## The `wings:draw` round

Four branches of the command had no test: the unknown id (102-104), the empty selection
(35-37), a wing that throws out of the drawing (60-63) and a wing with no image file yet
(132-134). Four tests in `tests/Feature/WingDrawCommandTest.php` cover them, and the
class's 29 mutants all left the uncovered list at once - it was the last whole class with
untested executable code.

Hand-checked, same way as before: `$skipped++` to `$skipped--`, the catch's `continue`
to `break`, removing either message, and removing `saveQuietly()` each fail the suite.
The third wing in the skip test exists for exactly that: `--all` orders by id, so leaving
the loop early has to show as a smaller drawn count rather than an identical report.

One mutant survives by construction: `substr(microtime(), 4, 2)` with a shifted offset
still yields two digits, and no assertion on the path can tell which two.

Still uncovered, and where the next round goes:

- `Wing` 71: all declarations - the `#[Fillable]`/`#[Appends]` lists and `IMAGE_VARIANTS`.
- `LoginController` 28: its validation arrays and guard branches (68-95), covered on the
  happy paths only.
- `WingDrawing` 34: 31 are constants and property defaults; 2 real lines, `$height = 1`
  (104) and the cg skip (232), which one fixture with a span but no chords reaches.
- `User` 13: 10 declarations, 1 return (87).
- `WingsController` 9: the guard at 160 and the image/report lines 161, 165.
- `Kernel` 17, `Handler` 12, `Authenticate` 6, the three providers 7, and three
  stragglers: framework glue, left alone.

Open questions, in place of the recommendation I first wrote here:

1. `#[Appends]` is load-bearing - removing one entry fails eleven tests - yet `toArray()`
   on a `Wing` raises `BadMethodCallException: Call to undefined method
   App\Wing::getPanel1spanAttribute()`, because the appends take Laravel down the old
   `getXAttribute` path while the panel values are `Attribute` casts. Both cannot be
   right at once and neither the appends nor the casts should be removed on a guess: the
   attribute is what the `saving` hook reads to drop the panel fields from the row, which
   is why it is not dead. Worth pinning with a test for whichever of the two is intended.
2. `prepPanelsArray()` is deleted, with the `method.unused` baseline entry that had already
   recorded it: no caller anywhere, 20 lines duplicating what the `saving` hook builds.

The drawing also gained a fixture no other test had - a wing that sweeps *forward*, the
only way `draw_panel`'s else branch runs. Reaching a line is not pinning it: the first
version of that test compared the topmost ink row of the two renders, and removing the
branch's span line by hand left it green. Asserting that line's own row kills the
mutation, re-checked the same way. The branch's arc is an equivalent mutant either way -
`arc($span * $radius, $angle, 0)` and its endpoints swapped trace the same curve - so it
stays alive whatever the test asserts.

Still uncovered, and where the next round goes:

- `WingDraw` 19: the `drawOne` guards (lines 35-63), the report lines (102-104) and the
  path checks (132-134). Command internals, reachable from `artisan wing:draw`.
- `WingDrawing` ~55: its class constants and property defaults, the same declaration class
  Pest cannot score as the Wing attributes. `GAP`, `LINE`, `TICK`, `CHORD_GAP` and
  `CG_GAP` are pinned anyway by the zoom-1 fixture, hand-checked above.
- `Kernel`, `Handler`, `Authenticate`, the providers: framework glue, left alone.

