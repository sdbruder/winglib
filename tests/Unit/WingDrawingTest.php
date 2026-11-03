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

it('refuses a wing with no panels', function () {
    $wing = drawingWing();
    $wing->panels = array_fill(1, 6, ['span' => '', 'chord' => '', 'sweep' => '', 'angle' => '']);

    expect(fn () => WingDrawing::png($wing))->toThrow(RuntimeException::class, 'no panels');
});
