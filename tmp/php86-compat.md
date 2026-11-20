# Wing Lib on PHP 8.6 — check report

Date: 2026-11-20. Tested PHP 8.6.0RC3 (Herd build, `php86`).

## Answer

Runtime: yes. Development install and CI: no, blocked by one dev dependency.

| Check | PHP 8.5.12 | PHP 8.6.0RC3 |
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

## The one failing test is not a PHP 8.6 change

`Tests\Unit\WingDrawingTest > it draws the measures at their own pixel when a model unit
is one pixel` fails at `tests/Unit/WingDrawingTest.php:453`. It expects the ink run
`[483, 485]` on row 50; 8.6 produces `[483, 487]` — the cg line merged with a text pixel
next to it.

Deterministic (two runs per version), and caused by the FreeType version in the PHP build:

| Build | GD | FreeType | `imagettfbbox(8, 0, DejaVuSans, '440 mm')` |
| --- | --- | --- | --- |
| Herd php85 | bundled 2.1.0 | 2.15.0 | height 8 |
| Herd php86 | bundled 2.4.0 | 2.14.3 | height 7 |

`WingDrawing::text()` derives the label strip height and baseline from that bbox, so one
row less of height moves every label one pixel. The test asserts exact label pixels, so it
is tied to the rasterizer build, not to the PHP language version. Any FreeType change
breaks it, including a Herd rebuild of 8.5.

Fix direction: pin that assertion to a tolerance the label box allows (or skip when
`gd_info()` FreeType differs), or accept the new baseline by regenerating the expected
runs once a runtime is chosen.

## Repro

```sh
export PATH="/Users/sergio/Library/Application Support/Herd/bin:$PATH"
php86 "$(which composer)" check-platform-reqs            # fails on paratest
php86 "$(which composer)" check-platform-reqs --no-dev   # passes
DB_CONNECTION=mysql DB_HOST=localhost DB_USERNAME=root DB_PASSWORD= \
  DB_DATABASE=winglib_test php86 vendor/bin/pest
```