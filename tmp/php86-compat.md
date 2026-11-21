# Wing Lib on PHP 8.6 — check report

Date: 2026-11-21. Tested PHP 8.6.0 final (Herd build 2026-11-19, `php86`).

## Answer

Runtime: yes. Development install and CI: yes, after the dev-stack upgrade on branch
`php86-update`.

| Check | PHP 8.5.13 | PHP 8.6.0 |
| --- | --- | --- |
| `composer check-platform-reqs --no-dev` | pass | pass |
| `composer check-platform-reqs` (with dev) | pass | pass |
| `composer update --dry-run` (with dev) | pass | pass |
| `php artisan about` | pass | pass |
| `php artisan route:list` | pass | pass, 24 routes |
| Pest suite (MySQL 8) | 94 pass | 94 pass |
| Deprecation notices, `E_ALL` | none | none |

## The original blocker: the PHPUnit 12 pin

Paratest supports PHP 8.6. Release v7.25.0 (2026-09-24) added it
(paratestphp/paratest#1138), and the current v7.29.0 (2026-11-14) declares
`php: ~8.4.0 || ~8.5.0 || ~8.6.0`. Its CI matrix tests 8.6 against both lowest and
highest dependencies. There is no paratest 8.x; "8.6" is PHP support.

The repo cannot reach those releases yet. Paratest v7.25.0 and later require
`phpunit/phpunit ^13.3.4`, but the root `composer.json` pins `phpunit/phpunit ^12.0`
and `pestphp/pest ^4.0`. Pest 4.7.8 requires PHPUnit `^12.5.33`, so Composer can only
select paratest up to v7.24.1 (or the locked v7.20.0), all of which cap PHP at 8.5.
That is the actual solver failure: not paratest's own PHP constraint.

Upgrade path, applied on branch `php86-update`:

| Package | Before | After |
| --- | --- | --- |
| `pestphp/pest` | 4.7.8 | 5.3.7 |
| `pestphp/pest-plugin-laravel` | 4.1.0 | 5.0.1 |
| `phpunit/phpunit` | 12.5.33 | 13.4.7 |
| `brianium/paratest` | 7.20.0 | 7.29.0 |
| `symfony/*` | 8.1.9/8.1.10 | 8.1.11 |

The full suite passes 94/94 on PHP 8.5.13 and 94/94 on PHP 8.6.0. `pint`, PHPStan and
`composer-unused` pass, and `composer audit` is clean after the Symfony 8.1.11 patch
bump. Costs: the dev floor rises from PHP 8.3 to 8.4 (`pest-plugin-laravel` 5.0.1
requires `^8.4`, PHPUnit 13 requires `>=8.4.1`). Production is unaffected: `--no-dev`
skips all of this and every runtime package accepts 8.6. `composer.json` (`"php":
"^8.2"`) already allows 8.6.

CI now runs on 8.6 (`.github/workflows/ci.yml`). The production deploy script still
pins PHP 8.5; the app is compatible with both.

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

Fixed on `php86-update` by moving the row-50 assertion to row 48, where the label does
not merge into the cg run on either rasterizer. The strict run widths are preserved.

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
php86 "$(which composer)" check-platform-reqs            # fails: locked paratest 7.20 caps at 8.5
php86 "$(which composer)" check-platform-reqs --no-dev   # passes
DB_CONNECTION=mysql DB_HOST=localhost DB_USERNAME=root DB_PASSWORD= \
  DB_DATABASE=winglib_test php86 vendor/bin/pest
# In a temp copy with pest ^5 and phpunit ^13:
php86 "$(which composer)" update --dry-run               # resolves paratest 7.29.0
```