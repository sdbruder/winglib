<?php

use App\Support\WingDrawing;
use App\Wing;

function drawingWing(array $overrides = []): Wing
{
    $wing = new Wing(array_merge([
        'unitsystem' => 'metric',
        'cgpos' => 20.0,
        'drawvalues' => 0,
        'rootchord' => 340.0,
    ], $overrides));

    $wing->panels = [
        1 => ['span' => '600', 'chord' => '180', 'sweep' => '300', 'angle' => '26.57'],
        2 => ['span' => '', 'chord' => '', 'sweep' => '', 'angle' => ''],
        3 => ['span' => '', 'chord' => '', 'sweep' => '', 'angle' => ''],
        4 => ['span' => '', 'chord' => '', 'sweep' => '', 'angle' => ''],
        5 => ['span' => '', 'chord' => '', 'sweep' => '', 'angle' => ''],
        6 => ['span' => '', 'chord' => '', 'sweep' => '', 'angle' => ''],
    ];

    return $wing;
}

function drawingPixel($image, int $x, int $y): array
{
    $colour = imagecolorat($image, $x, $y);

    return [($colour >> 24) & 0x7F, ($colour >> 16) & 0xFF, ($colour >> 8) & 0xFF, $colour & 0xFF];
}

function drawingInkNear($image, int $x, int $y): bool
{
    for ($dx = -2; $dx <= 2; $dx++) {
        for ($dy = -2; $dy <= 2; $dy++) {
            [$alpha, $red, $green, $blue] = drawingPixel($image, $x + $dx, $y + $dy);

            if ($alpha < 64 && $red < 160 && $green < 160 && $blue < 160) {
                return true;
            }
        }
    }

    return false;
}

function drawingInkBounds($image): array
{
    $width = imagesx($image);
    $height = imagesy($image);
    $bounds = ['left' => $width, 'top' => $height, 'right' => 0, 'bottom' => 0];

    for ($y = 1; $y < $height - 1; $y++) {
        for ($x = 1; $x < $width - 1; $x++) {
            [$alpha, $red, $green, $blue] = drawingPixel($image, $x, $y);

            if ($alpha > 63 && $red > 200 && $green > 200 && $blue > 200) {
                continue;
            }

            $bounds['left'] = min($bounds['left'], $x);
            $bounds['top'] = min($bounds['top'], $y);
            $bounds['right'] = max($bounds['right'], $x);
            $bounds['bottom'] = max($bounds['bottom'], $y);
        }
    }

    return $bounds;
}

it('draws the measures above a wing that sweeps forward', function () {
    // Every other fixture sweeps backwards, so draw_panel takes its else branch - the span line
    // above the *leading* edge rather than the swept one, and the arc from the angle back to zero -
    // and mutation testing never reached it. A small forward sweep keeps those lines on the canvas.
    $wing = drawingWing(['drawvalues' => 1]);
    $wing->panels = [
        1 => ['span' => '600', 'chord' => '180', 'sweep' => '-20', 'angle' => '-5'],
        2 => ['span' => '', 'chord' => '', 'sweep' => '', 'angle' => ''],
        3 => ['span' => '', 'chord' => '', 'sweep' => '', 'angle' => ''],
        4 => ['span' => '', 'chord' => '', 'sweep' => '', 'angle' => ''],
        5 => ['span' => '', 'chord' => '', 'sweep' => '', 'angle' => ''],
        6 => ['span' => '', 'chord' => '', 'sweep' => '', 'angle' => ''],
    ];

    $with = imagecreatefromstring(WingDrawing::png($wing));
    $without = imagecreatefromstring(WingDrawing::png(drawingWing(['drawvalues' => 0])));

    $zoom = min(880 / 1200, 340 / 480);
    $column = (int) round(470.0 + $zoom * 300);

    $withRows = drawingInkRows($with, $column);
    $withoutRows = drawingInkRows($without, $column);

    expect($withRows)->not->toBeEmpty();
    expect($withoutRows)->not->toBeEmpty();

    // The else branch hangs its span line at sweep - gap, i.e. 25 above the origin, and holds its
    // label four pixels above that. Column 300 is inside both, and the plain render has nothing
    // higher than the leading edge, which crosses that column ten above the origin.
    $spanLineRow = (int) round(30.0 + $zoom * -25);

    expect(drawingInkRows($with, $column))->toContain($spanLineRow);
    expect(drawingInkRows($without, $column))->not->toContain($spanLineRow);
});

it('draws the panels where the canvas geometry puts them', function () {
    $image = imagecreatefromstring(WingDrawing::png(drawingWing()));

    expect(imagesx($image))->toBe(940)->and(imagesy($image))->toBe(400);

    // The canvas sizes the drawing with zoom = min((940-60)/span*2, (400-60)/height), and the
    // origin sits at the canvas centre, one border below the top.
    $zoom = min(880 / 1200, 340 / 480);
    $originX = 470.0;
    $originY = 30.0;

    $corners = [
        [0, 0], [600, 300], [600, 480], [0, 340],
        [-600, 300], [-600, 480], [0, 340],
    ];

    foreach ($corners as [$x, $y]) {
        $px = (int) round($originX + $zoom * $x);
        $py = (int) round($originY + $zoom * $y);

        expect(drawingInkNear($image, $px, $py))->toBeTrue("no ink at the corner $x,$y ($px,$py)");
    }

    [$alpha, $red, $green, $blue] = drawingPixel($image, 700, 200);
    expect($alpha)->toBe(0)->and([$red, $green, $blue])->toBe([0xF4, 0xF6, 0xFF]);

    [$alpha] = drawingPixel($image, 60, 390);
    expect($alpha)->toBe(127);
});

it('skips a panel that has a span but no chords', function () {
    $wing = drawingWing();
    $wing->panels = [
        1 => ['span' => '600', 'chord' => '180', 'sweep' => '300', 'angle' => '26.57'],
        2 => ['span' => '400', 'chord' => '0', 'sweep' => '0', 'angle' => '0'],
        3 => ['span' => '', 'chord' => '', 'sweep' => '', 'angle' => ''],
        4 => ['span' => '', 'chord' => '', 'sweep' => '', 'angle' => ''],
        5 => ['span' => '', 'chord' => '', 'sweep' => '', 'angle' => ''],
        6 => ['span' => '', 'chord' => '', 'sweep' => '', 'angle' => ''],
    ];

    $image = imagecreatefromstring(WingDrawing::png($wing));

    expect(imagesx($image))->toBe(940)->and(imagesy($image))->toBe(400);
});

it('keeps the same relative margin on a smaller canvas', function () {
    $wing = drawingWing();

    $big = drawingInkBounds(imagecreatefromstring(WingDrawing::png($wing)));
    $small = drawingInkBounds(imagecreatefromstring(WingDrawing::png($wing, 188)));

    $bigShare = ($big['right'] - $big['left']) / 940;
    $smallShare = ($small['right'] - $small['left']) / 188;

    expect(abs($bigShare - $smallShare))->toBeLessThan(0.06);
    expect(abs(($big['bottom'] - $big['top']) / 400 - ($small['bottom'] - $small['top']) / 77))->toBeLessThan(0.06);
});

it('refuses a wing with no panels', function () {
    $wing = drawingWing();
    $wing->panels = array_fill(1, 6, ['span' => '', 'chord' => '', 'sweep' => '', 'angle' => '']);

    expect(fn () => WingDrawing::png($wing))->toThrow(RuntimeException::class, 'no panels');
});

it('renders a wing whose attributes have to fall back', function () {
    // Mutation testing kept surviving on the reads of unitsystem, drawvalues, cgpos and rootchord
    // in readPanels(), because every other test states those attributes outright. A wing saved
    // from a form that did not post them carries no attribute at all, and that is the case the
    // create and fork paths render with, so pin it: the fallbacks must give the same drawing as a
    // wing that states the same values.
    $partial = drawingWing();
    unset($partial['unitsystem'], $partial['cgpos'], $partial['drawvalues'], $partial['rootchord']);

    $stated = drawingWing(['cgpos' => 0.0, 'drawvalues' => 0, 'rootchord' => 0.0]);
    unset($stated['unitsystem']);

    $fromFallbacks = WingDrawing::png($partial);
    $fromStated = WingDrawing::png($stated);

    expect($fromFallbacks)->toStartWith("\x89PNG");
    expect($fromStated)->toStartWith("\x89PNG");
    expect($fromFallbacks)->toBe($fromStated);
});

/** True when the pixel carries the cg marker's own ink (0x36,0x36,0xB2), blended edges included. */
function drawingCgAt($image, int $x, int $y): bool
{
    [$alpha, $red, $green, $blue] = drawingPixel($image, $x, $y);

    return $alpha < 64 && abs($red - 0x36) <= 8 && abs($green - 0x36) <= 8 && abs($blue - 0xB2) <= 8;
}

/** The box the cg ink covers: the ball, its distance line and the MAC line. */
function drawingCgBounds($image): array
{
    $width = imagesx($image);
    $height = imagesy($image);
    $bounds = ['left' => $width, 'top' => $height, 'right' => 0, 'bottom' => 0];

    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            if (! drawingCgAt($image, $x, $y)) {
                continue;
            }

            $bounds['left'] = min($bounds['left'], $x);
            $bounds['top'] = min($bounds['top'], $y);
            $bounds['right'] = max($bounds['right'], $x);
            $bounds['bottom'] = max($bounds['bottom'], $y);
        }
    }

    return $bounds;
}

/** The rows the cg ink crosses a given column at, in order. */
function drawingCgRows($image, int $x): array
{
    $rows = [];

    for ($y = 0; $y < imagesy($image); $y++) {
        if (drawingCgAt($image, $x, $y)) {
            $rows[] = $y;
        }
    }

    return $rows;
}

/** The rows a column carries ink on, in order, for the lines that sit clear of everything else.
 *  The canvas border is skipped: it is ink on every column and says nothing about the drawing. */
function drawingInkRows($image, int $x): array
{
    $rows = [];

    for ($y = 2; $y < imagesy($image) - 2; $y++) {
        [$alpha, $red, $green, $blue] = drawingPixel($image, $x, $y);

        if ($alpha < 64 && $red < 160 && $green < 160 && $blue < 160) {
            $rows[] = $y;
        }
    }

    return $rows;
}

/** The contiguous ink runs in a row, as [first, last] pairs, for the ones clear of labels. */
function drawingInkRuns($image, int $y): array
{
    $runs = [];
    $start = null;
    $previous = null;

    for ($x = 2; $x < imagesx($image) - 2; $x++) {
        [$alpha, $red, $green, $blue] = drawingPixel($image, $x, $y);

        if ($alpha < 64 && $red < 160 && $green < 160 && $blue < 160) {
            $start ??= $x;
            $previous = $x;

            continue;
        }

        if ($start !== null) {
            $runs[] = [$start, $previous];
            $start = null;
        }
    }

    if ($start !== null) {
        $runs[] = [$start, $previous];
    }

    return $runs;
}

/** How many pixels inside a box are ink, for the label bands, where one glyph stroke is enough. */
function drawingInkCount($image, int $left, int $top, int $right, int $bottom): int
{
    $count = 0;

    for ($y = max(0, $top); $y <= $bottom; $y++) {
        for ($x = max(0, $left); $x <= $right; $x++) {
            [$alpha, $red, $green, $blue] = drawingPixel($image, $x, $y);

            if ($alpha < 64 && $red < 160 && $green < 160 && $blue < 160) {
                $count++;
            }
        }
    }

    return $count;
}

it('draws the cg ball, its distance line and the mac line', function () {
    // draw_cg puts three things in the cg ink: the MAC line at macdist 269.23, the horizontal cg
    // line at cg_dist 188.2, and the half filled ball at the origin. On this canvas the zoom is
    // min(880/1200, 340/480) = 0.708 and the origin is (470, 30), which puts those at x 661,
    // y 163, and a ball of max(5, round(2 * 0.708 * max(1.5 * 600 / 100, 5))) = 13 px across.
    $image = imagecreatefromstring(WingDrawing::png(drawingWing()));

    $zoom = min(880 / 1200, 340 / 480);
    $originX = 470.0;
    $originY = 30.0;

    $macLineX = (int) round($originX + $zoom * 269.23);
    $cgLineY = (int) round($originY + $zoom * 188.2);
    $leMacY = (int) round($originY + $zoom * 134.6);
    $teMacY = (int) round($originY + $zoom * 402.8);

    $bounds = drawingCgBounds($image);

    // The MAC line is the rightmost of the three and spans the leading to trailing mac y.
    expect($bounds['right'])->toBeBetween($macLineX - 2, $macLineX + 2);
    expect($bounds['top'])->toBeBetween($leMacY - 2, $leMacY + 2);
    expect($bounds['bottom'])->toBeBetween($teMacY - 2, $teMacY + 2);

    // The ball hangs off the origin towards the left, which nothing else in cg ink reaches.
    expect($bounds['left'])->toBeBetween((int) $originX - 9, (int) $originX - 5);
    expect(drawingCgAt($image, (int) $originX, $cgLineY))->toBeTrue();

    // Between the ball and the MAC line only the horizontal cg line crosses the column, at cg_dist.
    $rows = drawingCgRows($image, 560);
    expect($rows)->not->toBeEmpty();
    expect(min($rows))->toBeGreaterThanOrEqual($cgLineY - 2);
    expect(max($rows))->toBeLessThanOrEqual($cgLineY + 2);
});

it('draws the measure lines, labels and arc when the wing asks for them', function () {
    $with = imagecreatefromstring(WingDrawing::png(drawingWing(['drawvalues' => 1])));
    $without = imagecreatefromstring(WingDrawing::png(drawingWing(['drawvalues' => 0])));

    $zoom = min(880 / 1200, 340 / 480);
    $px = fn (float $x): int => (int) round(470.0 + $zoom * $x);
    $py = fn (float $y): int => (int) round(30.0 + $zoom * $y);

    // draw_panel hangs the span line one gap above the leading edge, and it is three pixels thick
    // (self::LINE). At x 500 nothing else has ink above the panels, so the rows are exact and a
    // one pixel shift in the gap or in the thickness shows up here.
    expect(drawingInkRows($with, $px(500)))->toContain($py(-5) - 1, $py(-5), $py(-5) + 1);
    expect(drawingInkRows($without, $px(500)))->not->toContain($py(-5) - 1, $py(-5), $py(-5) + 1);

    // The root chord line is inset from the leading edge, so it crosses the panel's fill.
    expect(drawingInkNear($with, $px(7), $py(170)))->toBeTrue();
    expect(drawingInkNear($without, $px(7), $py(170)))->toBeFalse();

    // The tip chord line sits beyond the wing's own edge, inset by the chord gap.
    expect(drawingInkNear($with, $px(607), $py(390)))->toBeTrue();
    expect(drawingInkNear($without, $px(607), $py(390)))->toBeFalse();

    // That line is the only thing that far right, and the rotated sweep label sits far above it,
    // so below row 200 the column states both of the line's ends exactly, ticks included.
    $tipChord = array_values(array_filter(drawingInkRows($with, $px(607)), fn (int $y): bool => $y > 200));
    expect($tipChord)->toBe(range($py(300) - 1, $py(480) + 1));
    expect(drawingInkRows($without, $px(607)))->toBe([]);

    // draw_cg's own line is inset by the cg gap and runs from the x axis out to cg_dist. On this
    // column the span line's tick ends at row 27 and the label starts at 166, so the run between
    // them is the cg line alone, and it states both of its ends.
    $cgLine = array_values(array_filter(drawingInkRows($with, $px(14)), fn (int $y): bool => $y > 27 && $y < 166));
    expect($cgLine)->toBe(range($py(0) - 1, $py(188.2) + 1));
    expect(drawingInkNear($without, $px(14), $py(94)))->toBeFalse();

    // The angle is 26.57 degrees, wider than the 15 the radius rule tests for, so the arc's
    // radius is 0.4 of the span and the arc starts on the x axis at 600 * 0.4 = 240.
    expect(drawingInkNear($with, $px(240), $py(0)))->toBeTrue();
    expect(drawingInkNear($without, $px(240), $py(0)))->toBeFalse();

    // The span label is centred above the span line, clear of the canvas border and of both ticks.
    expect(drawingInkCount($with, $px(300) - 40, 6, $px(300) + 40, 22))->toBeGreaterThan(0);
    expect(drawingInkCount($without, $px(300) - 40, 6, $px(300) + 40, 22))->toBe(0);

    // The sweep label is rotated a quarter turn beside the swept edge, past the line it labels.
    expect(drawingInkCount($with, $px(604) + 3, $py(100), $px(604) + 9, $py(200)))->toBeGreaterThan(0);
    expect(drawingInkCount($without, $px(604) + 3, $py(100), $px(604) + 9, $py(200)))->toBe(0);
});

/**
 * A wing 440 wide and 300 tall fills the canvas at zoom 1: min((940 - 60) / 880, (400 - 60) / 300)
 * is 1, so the origin is (470, 30) and one model unit is one pixel. Every other fixture is drawn
 * at zoom 0.708, where a one unit change in the pixel constants below is sub pixel and no pixel
 * test can see it, which is why the mutation run kept surviving on them.
 */
function drawingZoomOneWing(int $drawvalues): Wing
{
    $wing = drawingWing(['rootchord' => 300.0, 'drawvalues' => $drawvalues]);

    $wing->panels = [
        1 => ['span' => '440', 'chord' => '40', 'sweep' => '0', 'angle' => '0'],
        2 => ['span' => '', 'chord' => '', 'sweep' => '', 'angle' => ''],
        3 => ['span' => '', 'chord' => '', 'sweep' => '', 'angle' => ''],
        4 => ['span' => '', 'chord' => '', 'sweep' => '', 'angle' => ''],
        5 => ['span' => '', 'chord' => '', 'sweep' => '', 'angle' => ''],
        6 => ['span' => '', 'chord' => '', 'sweep' => '', 'angle' => ''],
    ];

    return $wing;
}

it('draws the measures at their own pixel when a model unit is one pixel', function () {
    $image = imagecreatefromstring(WingDrawing::png(drawingZoomOneWing(1)));

    // The span line: gap 5 above the leading edge at y 30, so rows 24 to 26 at line 3 thick, and
    // x 760 is the one stretch of it that is clear of the label and of both ticks.
    expect(array_slice(drawingInkRows($image, 760), 0, 3))->toBe([24, 25, 26]);

    // Its tick at the root is tick 4 either side of the line, and the left edge runs on from y 30.
    expect(array_slice(drawingInkRows($image, 470), 0, 9))->toBe(range(21, 29));

    // The tip chord line is off the wing, from the swept edge to the tip: y(0) to y(40) and its
    // ticks, and nothing else is at that column. The label below it starts at row 75.
    expect(array_slice(drawingInkRows($image, 917), 0, 43))->toBe(range(29, 71));
    expect(drawingInkRows(imagecreatefromstring(WingDrawing::png(drawingZoomOneWing(0))), 917))->toBe([]);

    // Row 50 crosses both inset lines and the edges, and the runs are exactly one line wide each:
    // the chord line at chord gap 7 and the cg line at cg gap 14. A one pixel move in either
    // constant moves its run off these positions.
    $runs = drawingInkRuns($image, 50);
    expect($runs)->toContain([469, 471]);
    expect($runs)->toContain([476, 478]);
    expect($runs)->toContain([483, 485]);

    // Row 200 crosses the wing itself: the mirrored wing's trailing edge, the left edge, the chord
    // line and the wing's own trailing edge, on both sides of the origin.
    expect(drawingInkRuns($image, 200))->toBe([[248, 252], [469, 471], [476, 478], [688, 692]]);

    // At row 65 the cg ball is drawn over the left edge, so the edge's run is no longer there.
    expect(drawingInkRuns($image, 65))->not->toContain([469, 471]);
});

it('draws none of the measures and puts the cg inks where the geometry puts them', function () {
    $image = imagecreatefromstring(WingDrawing::png(drawingZoomOneWing(0)));

    // Without the measures the rows and runs that carry them are absent, and nothing else moves:
    // the same rows at x 760 keep only the leading and trailing edges of the panel.
    expect(drawingInkRows($image, 760))->toBe([29, 30, 31, 158, 159, 160]);
    expect(drawingInkRuns($image, 200))->toBe([[248, 252], [469, 471], [688, 692]]);
    expect(drawingInkRuns($image, 50))->toBe([[29, 31], [469, 471], [909, 911]]);

    // The cg inks are the only ones this drawing always has: the ball at the origin (13 across, so
    // 464 to 476), the cg line out to cg_dist 40.64 at row 71, and the MAC line at macdist 163.9
    // running from the leading to the trailing mac y.
    expect(drawingCgBounds($image))->toBe(['left' => 464, 'top' => 30, 'right' => 635, 'bottom' => 233]);
});
