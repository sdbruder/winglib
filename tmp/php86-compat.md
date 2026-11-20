# Wing Lib on PHP 8.6 — check report

Date: 2026-11-20. Tested PHP 8.6.0 final (Herd build 2026-11-19, `php86`).

## Answer

Runtime: yes. Development install and CI: no, blocked by one dev dependency.

| Check | PHP 8.5.13 | PHP 8.6.0 |
| --- | --- | --- |
| `composer check-platform-reqs --no-dev` | pass | **pass** |
| `composer check-platform-reqs` (with dev) | pass | **fail: brianium/paratest** |
| `composer update --dry-run` (with dev) | pass | **fail: no solver solution** |
| `php artisan about` | pass | pass |
| `php artisan route:list` | pass | pass, 24 routes |
| Pest suite (MySQL 8) | 94 pass | 93 pass, 1 fail |
| Deprecation notices, `E_ALL` | none | none |

## Blocker: paratest caps PHP at 8.5

`brianium/paratest` is required by `pestphp/pest`, which is in `require-dev`. Every 7.x
release pins `php` to `~8.3.0 || ~8.4.0 || ~8.5.0` (v7.20.0 locked here; v7.29.0 is the
latest and has the same cap). On 8.6 Composer cannot resolve a dev install, so
`composer install` (as the CI workflow runs it) and `composer update` both fail.

Production installs are unaffected: `--no-dev` skips paratest and every runtime package
accepts 8.6. `composer.json` (`"php": "^8.2"`) already allows 8.6.

Options, in order of preference:

1. Wait for a paratest release that accepts 8.6, then raise CI to 8.6.
2. Keep CI at 8.5 and run the app on 8.6 where PHP 8.6 is the deployed runtime
   (`deploy.sh` and `.github/workflows/ci.yml` both pin 8.5 today).
3. Not recommended: `config.platform.php = 8.5` hides the constraint instead of
   solving it.

## The one failing test comes from the libgd 2.4 sync

`Tests\Unit\WingDrawingTest > it draws the measures at their own pixel when a model unit
is one pixel` fails at `tests/Unit/WingDrawingTest.php:453`. It expects the ink run
`[483, 485]` on row 50; 8.6 produces `[483, 487]` — the cg line merged with a text pixel
next to it.

Deterministic (two runs per version). Cause: PHP 8.6 is the first release with the
bundled libgd synced to upstream 2.4 (php-src PR #22532, merged 2026-07-16,
+142,450/−32,475 lines, labelled "ABI break"). That sync merged libgd's new FreeType
encoding into `gdft.c`, which changes text advances and `imagettfbbox()` boxes.

`WingDrawing::text()` derives the label strip height and baseline from that bbox, so the
labels move one row and shrink horizontally.

Survey of every Herd build on this machine isolates the variable:

| PHP | bundled GD | FreeType | `imagettfbbox(8,0,DejaVuSans,'440 mm')` |
| --- | --- | --- | --- |
| 8.0.30, 8.1.34 | 2.1.0 compatible | 2.13.2 | 46x8 |
| 8.2.36, 8.3.37, 8.4.28, 8.5.13 | 2.1.0 compatible | 2.15.0 | 46x8 |
| 8.6.0 | **2.4.0 compatible** | 2.15.0 | **43x7** |

FreeType moves between 2.13.2 and 2.15.0 with no change in the box; only the libgd sync
does. Glyph advances shift left, up to 3 px at size 8: ink column runs for `440 mm` at
size 8 are `[11-15],[18-22],[36],[47]` on 8.5 and `[11-15],[17-21],[34],[44]` on 8.6.

Fix direction: pin that assertion to a tolerance the label box allows, or regenerate the
expected runs once a runtime is chosen. The value is not portable across PHP 8.6 and
earlier, so a single expected set cannot pass on both.

## Two independent version axes

The PHP binary combines two libraries that move on separate schedules:

- libgd is vendored inside the php-src tarball (`ext/gd/libgd`), so it moves only when a
  PHP minor syncs it. That happened for 8.6, first time in years.
- FreeType is an external library linked statically from Herd's buildroot
  (`--with-openssl=.../buildroot`, no `libfreetype` in `otool -L`), so it moves whenever
  Herd rebuilds, on the buildroot's own schedule.

Herd's 8.6.0RC3 (built 2026-10-09) linked FreeType 2.14.3. The 8.6.0 final build
(2026-11-19) links 2.15.0, the same as 8.5.13. The 43x7 box does not change between the
RC and final build, so FreeType 2.15.0 is not the cause; the libgd 2.4 sync is. The
remaining skew is between libgd 2.1.0 and 2.4.0, not FreeType.

The sync also carries the "ABI break" label: any third-party extension compiled against
8.5's gd headers must be rebuilt for 8.6.

## Repro

```sh
export PATH="/Users/sergio/Library/Application Support/Herd/bin:$PATH"
php86 "$(which composer)" check-platform-reqs            # fails on paratest
php86 "$(which composer)" check-platform-reqs --no-dev   # passes
DB_CONNECTION=mysql DB_HOST=localhost DB_USERNAME=root DB_PASSWORD= \
  DB_DATABASE=winglib_test php86 vendor/bin/pest
```