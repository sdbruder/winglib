<?php

namespace App\Support;

use App\Wing;
use GdImage;
use RuntimeException;

/**
 * Rebuilds a wing's planform image from the wing's own data.
 *
 * The editor draws this on a canvas in public/js/winglib.js and posts the PNG, so a lost or
 * broken file has no source to re-upload: the measurements in the row are the source. Every
 * primitive here mirrors one function of that file — load_panels, draw_wing, draw_panel,
 * draw_line, draw_text, draw_cg — and the canvas geometry is reproduced 1:1: that context is
 * scaled by zoom, so its divisions by zoom collapse into the pixel constants below.
 */
class WingDrawing
{
    /** The canvas the editor posts, and the size of every stored image. */
    public const WIDTH = 940;

    private const HEIGHT = 400;

    /** draw_wing's border_size, the margin kept on every side. */
    private const BORDER = 30;

    private const LINE = 3;         // 3/zoom in the scaled context

    private const TICK = 4;         // 4/zoom, the tick half length

    private const GAP = 5;          // 5/zoom, the span line's offset from the leading edge

    private const CHORD_GAP = 7;    // 7/zoom, the chord lines' inset

    private const CG_GAP = 14;      // 14/zoom, the cg line's inset

    /** 8.625/zoom pt inside a zoom-scaled context is 11.5 px on the image. */
    private const TEXT_SIZE = 11.5;

    private const FONT = __DIR__.'/../../resources/fonts/DejaVuSans.ttf';

    private GdImage $image;

    private int $fill;

    private int $ink;

    private int $cgInk;

    private int $canvasWidth;

    private int $canvasHeight;

    private float $zoom = 1.0;

    private float $originX = 0.0;

    private float $originY = 0.0;

    /** @var array<int, array<string, float>> */
    private array $panels = [];

    private float $cgPos = 0.0;

    private bool $measures = false;

    private bool $metric = true;

    private function __construct(private readonly Wing $wing, int $width, private readonly ?bool $measuresOverride)
    {
        $this->canvasWidth = max(1, min($width, self::WIDTH));
        $this->canvasHeight = max(1, (int) round($this->canvasWidth * self::HEIGHT / self::WIDTH));
    }

    /**
     * The PNG bytes for this wing. The default size is the canvas the editor posts; a smaller
     * width draws the same planform on a smaller canvas, keeping the pixel weights above, which
     * is what a thumbnail needs to stay legible. A width below the default also drops the
     * measure labels unless $measures says otherwise.
     */
    public static function png(Wing $wing, int $width = self::WIDTH, ?bool $measures = null): string
    {
        return (new self($wing, $width, $measures))->render();
    }

    private function render(): string
    {
        $this->panels = $this->readPanels();

        if ($this->measuresOverride !== null) {
            $this->measures = $this->measuresOverride;
        }

        if ($this->panels[0]['span'] <= 0) {
            throw new RuntimeException('this wing has no panels to draw');
        }

        $whole = $this->panels[0];
        $width = $whole['span'] * 2;
        $height = max($whole['root'], $whole['sweep'] + $whole['tipc'], $whole['maxX'] - $whole['minX']);

        if ($height <= 0) {
            $height = 1;
        }

        $this->zoom = min(($this->canvasWidth - 2 * self::BORDER) / $width, ($this->canvasHeight - 2 * self::BORDER) / $height);
        $this->originX = $this->canvasWidth / 2;
        $this->originY = self::BORDER + ($whole['minX'] < 0 ? $this->zoom * abs($whole['minX']) : 0);

        $this->image = imagecreatetruecolor($this->canvasWidth, $this->canvasHeight);
        $this->fill = imagecolorallocate($this->image, 0xF4, 0xF6, 0xFF);
        $this->ink = imagecolorallocate($this->image, 0, 0, 0);
        $this->cgInk = imagecolorallocate($this->image, 0x36, 0x36, 0xB2);

        // The canvas is never filled, so whatever the panels do not cover stays transparent, and
        // that is what every stored image looks like. The panels paint the light fill on top.
        $this->clear();
        imagerectangle($this->image, 0, 0, $this->canvasWidth - 1, $this->canvasHeight - 1, $this->ink);

        $quantity = (int) $whole['qty'];

        for ($i = 1; $i <= $quantity; $i++) {
            $panel = $this->panels[$i];
            $this->panel($panel['span'], $panel['sweep'], $panel['root'], $panel['tipc'], $panel['angle'], $this->measures, $i === $quantity);
            $this->translate($panel['span'], $panel['sweep']);
        }

        $this->translate(-$whole['span'], -$whole['sweep']);

        for ($i = 1; $i <= $quantity; $i++) {
            $panel = $this->panels[$i];
            $this->panel(-$panel['span'], $panel['sweep'], $panel['root'], $panel['tipc'], $panel['angle'], false, false);
            $this->translate(-$panel['span'], $panel['sweep']);
        }

        $this->translate($whole['span'], -$whole['sweep']);

        $this->centerOfGravity();

        ob_start();
        imagepng($this->image, null, 9);
        $png = ob_get_clean();

        return $png === false ? '' : $png;
    }

    /**
     * load_panels: panel 0 is the whole wing, panels 1..6 carry span, chord, sweep and angle.
     *
     * @return array<int, array<string, float>>
     */
    private function readPanels(): array
    {
        $this->metric = $this->wing->unitsystem === 'metric';
        $this->measures = (bool) $this->wing->drawvalues;
        $this->cgPos = (float) $this->wing->getAttribute('cgpos');

        /** @var array<int|string, array<string, string>> $stored */
        $stored = (array) $this->wing->panels;

        $panels = [0 => [
            'span' => 0.0, 'root' => 0.0, 'tipc' => 0.0, 'sweep' => 0.0,
            'maxX' => 0.0, 'minX' => 0.0, 'angle' => 0.0, 'qty' => 0.0,
        ]];

        $last = 0;

        for ($i = 1; $i <= 6; $i++) {
            $row = $stored[$i] ?? $stored[(string) $i] ?? [];

            $panel = [
                'span' => (float) ($row['span'] ?? 0),
                'tipc' => (float) ($row['chord'] ?? 0),
                'sweep' => (float) ($row['sweep'] ?? 0),
                'angle' => (float) ($row['angle'] ?? 0),
                'root' => $i === 1 ? (float) $this->wing->rootchord : $panels[$i - 1]['tipc'],
            ];

            $previousSweep = $panels[0]['sweep'];
            $panels[0]['span'] += $panel['span'];
            $panels[0]['sweep'] += $panel['sweep'];
            $panels[0]['maxX'] = max($panels[0]['maxX'], $panel['root'], $panel['tipc'] + $panels[0]['sweep']);
            $panels[0]['minX'] = min($panels[0]['minX'], $previousSweep, $panels[0]['sweep']);

            if ($panel['span'] > 0) {
                $last = $i;
            }

            $panels[$i] = $panel;
        }

        $panels[0]['qty'] = (float) $last;
        $panels[0]['root'] = $last > 0 ? $panels[1]['root'] : 0.0;
        $panels[0]['tipc'] = $last > 0 ? $panels[$last]['tipc'] : 0.0;

        return $this->withCenterOfGravity($panels, $last);
    }

    /**
     * The rest of load_panels: each panel's MAC and cg, then the area weighted whole wing.
     *
     * @param  array<int, array<string, float>>  $panels
     * @return array<int, array<string, float>>
     */
    private function withCenterOfGravity(array $panels, int $last): array
    {
        $rootX = 0.0;
        $rootY = 0.0;

        $panels[0]['wingarea'] = 0.0;
        $panels[0]['mac_x'] = 0.0;
        $panels[0]['le_mac_y'] = 0.0;
        $panels[0]['te_mac_y'] = 0.0;

        for ($i = 1; $i <= $last; $i++) {
            $panel = $panels[$i];

            if ($panel['span'] <= 0) {
                continue;
            }

            $area = (($panel['root'] + $panel['tipc']) / 2) * $panel['span'];
            $leSlope = $panel['sweep'] / $panel['span'];
            $teSlope = (($panel['sweep'] + $panel['tipc']) - $panel['root']) / $panel['span'];

            $helperZero = -$panel['tipc'];
            $helperSlope0 = (($panel['sweep'] + $panel['tipc'] + $panel['root']) - $helperZero) / $panel['span'];
            $helperOne = $panel['root'] + $panel['tipc'];
            $helperSlope1 = (($panel['sweep'] - $panel['root']) - $helperOne) / $panel['span'];

            $macX = ($helperOne - $helperZero) / ($helperSlope0 - $helperSlope1);
            $leMacY = $leSlope * $macX;
            $teMacY = $teSlope * $macX + $panel['root'];

            $panels[$i]['wingarea'] = $area;
            $panels[$i]['mac_x'] = $macX;
            $panels[$i]['le_mac_y'] = $leMacY;
            $panels[$i]['te_mac_y'] = $teMacY;
            $panels[$i]['cg_dist'] = $leMacY + ($teMacY - $leMacY) * $this->cgPos / 100;
            $panels[$i]['maclen'] = $teMacY - $leMacY;
            $panels[$i]['macdist'] = $macX;

            $panels[0]['wingarea'] += $area;
            $panels[0]['mac_x'] += ($rootX + $macX) * $area;
            $panels[0]['le_mac_y'] += ($rootY + $leMacY) * $area;
            $panels[0]['te_mac_y'] += ($rootY + $teMacY) * $area;

            $rootX += $panel['span'];
            $rootY += $panel['sweep'];
        }

        if ($panels[0]['wingarea'] > 0) {
            $panels[0]['mac_x'] /= $panels[0]['wingarea'];
            $panels[0]['le_mac_y'] /= $panels[0]['wingarea'];
            $panels[0]['te_mac_y'] /= $panels[0]['wingarea'];
        }

        $panels[0]['cg_dist'] = $panels[0]['le_mac_y'] + ($panels[0]['te_mac_y'] - $panels[0]['le_mac_y']) * $this->cgPos / 100;
        $panels[0]['maclen'] = $panels[0]['te_mac_y'] - $panels[0]['le_mac_y'];
        $panels[0]['macdist'] = $panels[0]['mac_x'];

        return $panels;
    }

    private function translate(float $x, float $y): void
    {
        $this->originX += $this->zoom * $x;
        $this->originY += $this->zoom * $y;
    }

    private function clear(): void
    {
        imagealphablending($this->image, false);
        imagesavealpha($this->image, true);
        imagefill($this->image, 0, 0, imagecolorallocatealpha($this->image, 0, 0, 0, 127));
        imagealphablending($this->image, true);
    }

    private function x(float $x): int
    {
        return (int) round($this->originX + $this->zoom * $x);
    }

    private function y(float $y): int
    {
        return (int) round($this->originY + $this->zoom * $y);
    }

    /** draw_panel: the panel's quadrilateral, then its measures on the right half only. */
    private function panel(float $span, float $sweep, float $root, float $tipc, float $angle, bool $measures, bool $tip): void
    {
        $points = [
            $this->x(0), $this->y(0),
            $this->x($span), $this->y($sweep),
            $this->x($span), $this->y($sweep + $tipc),
            $this->x(0), $this->y($root),
        ];

        imagefilledpolygon($this->image, $points, $this->fill);
        imagesetthickness($this->image, self::LINE);
        imagepolygon($this->image, $points, $this->ink);

        if (! $measures) {
            return;
        }

        $unit = $this->metric ? ' mm' : ' in';
        $radius = abs($angle) < 15 ? 0.7 : 0.4;

        if ($sweep >= 0) {
            $this->dimension(0, -self::GAP, $span, -self::GAP, 2, self::num($span).$unit);
            $this->dimension($span, 0, $span, $sweep, 2, self::num($sweep).$unit);
            $this->arc($span * $radius, 0, $angle);
        } else {
            $this->dimension(0, $sweep - self::GAP, $span, $sweep - self::GAP, 2, self::num($span).$unit);
            $this->dimension(0, 0, 0, $sweep, 2, self::num($sweep).$unit);
            $this->arc($span * $radius, $angle, 0);
        }

        $this->text($span * ($radius + 0.03), 0.7 * $radius * $sweep, 0, 'left', self::num($angle).'º');
        $this->dimension(self::CHORD_GAP, 0, self::CHORD_GAP, $root, 4, self::num($root).$unit);

        if ($tip) {
            $this->dimension($span + self::CHORD_GAP, $sweep, $span + self::CHORD_GAP, $sweep + $tipc, 2, self::num($tipc).$unit);
        }
    }

    /** draw_line: the line, a tick at each end, and the label where draw_line puts it. */
    private function dimension(float $x1, float $y1, float $x2, float $y2, int $position, string $label): void
    {
        imagesetthickness($this->image, self::LINE);

        if ($y1 == $y2) {
            imageline($this->image, $this->x($x1), $this->y($y1) - self::TICK, $this->x($x1), $this->y($y1) + self::TICK, $this->ink);
            imageline($this->image, $this->x($x2), $this->y($y2) - self::TICK, $this->x($x2), $this->y($y2) + self::TICK, $this->ink);

            if ($label !== '') {
                $this->text(($x1 + $x2 * ($position - 1)) / $position, $y1 - self::TICK, 0, 'center', $label);
            }
        } elseif ($x1 == $x2) {
            imageline($this->image, $this->x($x1) - self::TICK, $this->y($y1), $this->x($x1) + self::TICK, $this->y($y1), $this->ink);
            imageline($this->image, $this->x($x2) - self::TICK, $this->y($y2), $this->x($x2) + self::TICK, $this->y($y2), $this->ink);

            if ($label !== '') {
                $this->text($x1 + self::TICK, ($y1 + ($position - 1) * $y2) / $position, M_PI / 2, 'center', $label);
            }
        }

        imageline($this->image, $this->x($x1), $this->y($y1), $this->x($x2), $this->y($y2), $this->ink);
    }

    private function arc(float $radius, float $from, float $to): void
    {
        if (abs($from - $to) < 0.001) {
            // The canvas draws nothing for a zero length arc, and GD would draw a whole ellipse.
            return;
        }

        imagesetthickness($this->image, self::LINE);
        imagearc(
            $this->image,
            $this->x(0), $this->y(0),
            (int) round(2 * $this->zoom * $radius),
            (int) round(2 * $this->zoom * $radius),
            (int) round($from),
            (int) round($to),
            $this->ink
        );
    }

    /** draw_cg: the MAC, its distance from the root chord, and the half filled cg ball. */
    private function centerOfGravity(): void
    {
        $whole = $this->panels[0];
        $radius = max(1.5 * $whole['span'] / 100, $this->metric ? 5 : 0.2);

        // A thumbnail shrinks the ball with everything else; at a couple of pixels GD draws
        // nothing sensible for a zero diameter ellipse, so it keeps a visible dot.
        $diameter = max(5, (int) round(2 * $this->zoom * $radius));
        $centreX = $this->x(0);
        $centreY = $this->y($whole['cg_dist']);

        imagesetthickness($this->image, self::LINE);

        imageline($this->image, $this->x($whole['mac_x']), $this->y($whole['le_mac_y']), $this->x($whole['mac_x']), $this->y($whole['te_mac_y']), $this->cgInk);
        imageline($this->image, $centreX, $centreY, $this->x($whole['mac_x']), $centreY, $this->cgInk);

        imageellipse($this->image, $centreX, $centreY, $diameter, $diameter, $this->cgInk);
        imagefilledarc($this->image, $centreX, $centreY, $diameter, $diameter, 0, 90, $this->cgInk, IMG_ARC_PIE);
        imagefilledarc($this->image, $centreX, $centreY, $diameter, $diameter, 180, 270, $this->cgInk, IMG_ARC_PIE);

        if ($this->measures) {
            $unit = $this->metric ? ' mm' : ' in';
            $this->dimension(self::CG_GAP, 0, self::CG_GAP, $whole['cg_dist'], 2, 'CG@'.self::num($this->cgPos).'% '.self::num($whole['cg_dist']).$unit);
        }
    }

    /** draw_text: the canvas rotates about the anchor and aligns the text to it. */
    private function text(float $x, float $y, float $rotate, string $align, string $label): void
    {
        $box = imagettfbbox(self::TEXT_SIZE, 0, self::FONT, $label);
        $width = (int) ceil($box[2] - $box[0]) + 4;
        $height = (int) ceil($box[1] - $box[7]) + 4;
        $baseline = $height - 3;

        $strip = imagecreatetruecolor($width, $height);
        $transparent = imagecolorallocatealpha($strip, 0, 0, 0, 127);
        imagealphablending($strip, false);
        imagesavealpha($strip, true);
        imagefill($strip, 0, 0, $transparent);
        imagealphablending($strip, true);
        imagettftext($strip, self::TEXT_SIZE, 0, 2, $baseline, imagecolorallocatealpha($strip, 0, 0, 0, 0), self::FONT, $label);

        $rotated = abs($rotate) > 0.001;

        if ($rotated) {
            // GD turns counter clockwise for positive angles where the canvas turns clockwise,
            // and afterwards the baseline no longer means anything, so the strip is centred.
            $strip = imagerotate($strip, -rad2deg($rotate), $transparent);
            imagesavealpha($strip, true);
        }

        $width = imagesx($strip);
        $height = imagesy($strip);
        $left = $align === 'center' ? $this->x($x) - (int) round($width / 2) : $this->x($x);
        $top = $rotated ? $this->y($y) - (int) round($height / 2) : $this->y($y) - $baseline;

        imagecopy($this->image, $strip, $left, $top, 0, 0, $width, $height);
        imagedestroy($strip);
    }

    /** JavaScript prints 600, 26.57 and 0.7 where PHP prints 600.00, 26.57 and 0.70. */
    private static function num(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }
}
