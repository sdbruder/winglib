<?php

namespace App;

use App\Support\WingDrawing;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Throwable;

define('URL_SEPARATOR', '/');

#[Appends([
    'panel1span',
    'panel1chord',
    'panel1sweep',
    'panel1angle',

    'panel2span',
    'panel2chord',
    'panel2sweep',
    'panel2angle',

    'panel3span',
    'panel3chord',
    'panel3sweep',
    'panel3angle',

    'panel4span',
    'panel4chord',
    'panel4sweep',
    'panel4angle',

    'panel5span',
    'panel5chord',
    'panel5sweep',
    'panel5angle',

    'panel6span',
    'panel6chord',
    'panel6sweep',
    'panel6angle',
])]
#[Fillable([
    'slug',
    'user_id',
    'name',
    'description',
    'image_file',
    'unitsystem',
    'cgpos',
    'weight',
    'drawvalues',
    'rootchord',
    'totalspan',
    'panels',
    'wingarea',
    'macdist',
    'maclength',
    'cg',
    'wingload',

    'panel1span',
    'panel1chord',
    'panel1sweep',
    'panel1angle',

    'panel2span',
    'panel2chord',
    'panel2sweep',
    'panel2angle',

    'panel3span',
    'panel3chord',
    'panel3sweep',
    'panel3angle',

    'panel4span',
    'panel4chord',
    'panel4sweep',
    'panel4angle',

    'panel5span',
    'panel5chord',
    'panel5sweep',
    'panel5angle',

    'panel6span',
    'panel6chord',
    'panel6sweep',
    'panel6angle',
])]
class Wing extends Model
{
    use HasFactory;

    /**
     * boot method
     */
    protected static function boot()
    {
        parent::boot();

        // construct the panels json
        static::saving(function (Wing $wing) {
            if (array_key_exists('panel1span', $wing->attributes)) {
                $wing->panels = [
                    1 => ['span' => (float) $wing->attributes['panel1span'],
                        'chord' => (float) $wing->attributes['panel1chord'],
                        'sweep' => (float) $wing->attributes['panel1sweep'],
                        'angle' => (float) $wing->attributes['panel1angle']],
                    2 => ['span' => (float) $wing->attributes['panel2span'],
                        'chord' => (float) $wing->attributes['panel2chord'],
                        'sweep' => (float) $wing->attributes['panel2sweep'],
                        'angle' => (float) $wing->attributes['panel2angle']],
                    3 => ['span' => (float) $wing->attributes['panel3span'],
                        'chord' => (float) $wing->attributes['panel3chord'],
                        'sweep' => (float) $wing->attributes['panel3sweep'],
                        'angle' => (float) $wing->attributes['panel3angle']],
                    4 => ['span' => (float) $wing->attributes['panel4span'],
                        'chord' => (float) $wing->attributes['panel4chord'],
                        'sweep' => (float) $wing->attributes['panel4sweep'],
                        'angle' => (float) $wing->attributes['panel4angle']],
                    5 => ['span' => (float) $wing->attributes['panel5span'],
                        'chord' => (float) $wing->attributes['panel5chord'],
                        'sweep' => (float) $wing->attributes['panel5sweep'],
                        'angle' => (float) $wing->attributes['panel5angle']],
                    6 => ['span' => (float) $wing->attributes['panel6span'],
                        'chord' => (float) $wing->attributes['panel6chord'],
                        'sweep' => (float) $wing->attributes['panel6sweep'],
                        'angle' => (float) $wing->attributes['panel6angle']],
                ];
            }
            if (! $wing->slug) {
                $slug_str = Str::slug($wing->name);
                $latest = Wing::whereRaw("slug_str = '{$slug_str}'")->latest('slug_id')->get()->first();
                if ($latest) {
                    $slug_id = $latest['slug_id'] + 1;
                    $wing->attributes['slug_str'] = $slug_str;
                    $wing->attributes['slug_id'] = $slug_id;
                    $wing->attributes['slug'] = $slug_str.'-'.$slug_id;
                } else {
                    $wing->attributes['slug_str'] = $slug_str;
                    $wing->attributes['slug'] = $slug_str;
                }
            }
            $wing->attributes['totalspan'] =
                (
                    floatval($wing->panels[1]['span']) +
                    floatval($wing->panels[2]['span']) +
                    floatval($wing->panels[3]['span']) +
                    floatval($wing->panels[4]['span']) +
                    floatval($wing->panels[5]['span']) +
                    floatval($wing->panels[6]['span'])
                ) * 2;

            foreach ($wing->attributes as $key => $value) {
                if (in_array($key, $wing->appends)) {
                    unset($wing->attributes[$key]);
                }
            }

            return true;
        }); // static::saving

        // The home panels and the library listing show a small drawing beside each wing. It is
        // rendered server side from the same data as the full size image, so refreshing it here
        // covers every write path at once: store, update, fork, and the drawing command.
        static::saved(function (Wing $wing) {
            $wing->refreshImageVariants();
        });
    }

    public static function duplicateImage($orig_image)
    {
        $subdir = substr(microtime(), 4, 2);
        $storage_path = implode(DIRECTORY_SEPARATOR, [storage_path(), 'app', 'wingimg', $subdir]);
        $url_path = implode(URL_SEPARATOR, ['wingimg', $subdir]);
        $filename = uniqid('').'.png';

        $image_filename = implode(DIRECTORY_SEPARATOR, [$storage_path, $filename]);
        $image_url = URL_SEPARATOR.implode(URL_SEPARATOR, [$url_path, $filename]);

        $origin = implode(DIRECTORY_SEPARATOR, [storage_path(), 'app', $orig_image]);
        @copy($origin, $image_filename);

        // return the URL made above.
        return $image_url;
    }

    public static function createImage($base64_image)
    {

        $subdir = substr(microtime(), 4, 2);
        $storage_path = implode(DIRECTORY_SEPARATOR, [storage_path(), 'app', 'wingimg', $subdir]);
        $url_path = implode(URL_SEPARATOR, ['wingimg', $subdir]);
        $filename = uniqid('').'.png';

        $image_filename = implode(DIRECTORY_SEPARATOR, [$storage_path, $filename]);
        $image_url = URL_SEPARATOR.implode(URL_SEPARATOR, [$url_path, $filename]);

        // get the base-64 from data
        $base64_str = substr($base64_image, strpos($base64_image, ',') + 1);
        // decode base64 string and write it down.
        $image = base64_decode($base64_str);
        file_put_contents($image_filename, $image);

        // return the URL made above.
        return $image_url;
    }

    public function saveImage($base64_image)
    {
        $image_filename = implode(DIRECTORY_SEPARATOR, [storage_path(), 'app']).$this->image_file;
        // get the base-64 from data
        $base64_str = substr($base64_image, strpos($base64_image, ',') + 1);
        // decode base64 string and write it down.
        file_put_contents($image_filename, base64_decode($base64_str));
    }

    /** The thumbnail widths the home panels and the library listing ask for. */
    public const IMAGE_VARIANTS = [180, 282];

    /**
     * (Re)draw the thumbnails the views ask for, from this wing's own measurements. It runs on
     * every save, so no write path — created, edited, forked, redrawn — can leave a wing with a
     * missing or a stale thumbnail, and it never touches the full size image the editor posted.
     */
    public function refreshImageVariants(): void
    {
        if ($this->image_file === '' || ! file_exists(storage_path('app'.$this->image_file))) {
            return;
        }

        foreach (self::IMAGE_VARIANTS as $width) {
            try {
                // No measure labels at thumbnail size: they would only smear.
                $png = WingDrawing::png($this, $width, false);
            } catch (Throwable) {
                return; // a wing that cannot be drawn must not break the save that produced it
            }

            $path = storage_path('app'.$this->imageVariant($width));

            File::ensureDirectoryExists(dirname($path));
            File::put($path, $png);
        }
    }

    /**
     * The image_file beside this one for a smaller canvas, e.g. /wingimg/52/x-90.png. The
     * drawing command writes those, and the views use them where they show a thumbnail.
     */
    public function imageVariant(int $width): string
    {
        if ($this->image_file === '' || $width >= WingDrawing::WIDTH) {
            return (string) $this->image_file;
        }

        return preg_replace('/\.png$/', '-'.$width.'.png', $this->image_file) ?? $this->image_file;
    }

    public function unit($what)
    {
        if ($this->unitsystem == 'metric') {
            $units = ['distance' => 'mm',
                'weight' => 'g',
                'area' => 'dm²',
                'load' => 'g/dm²',
                'percentual' => '%',
                'angle' => '°'];
        } else {
            $units = ['distance' => 'in',
                'weight' => 'oz',
                'area' => 'in²',
                'load' => 'oz/ft²',
                'percentual' => '%',
                'angle' => '°'];
        }

        return $units[$what];
    }

    public function endsWith($haystack, $needle)
    {
        // search forward starting from end minus needle length characters
        return $needle === '' || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
    }

    public function clearDotZeroes($value)
    {
        if ($this->endsWith($value, '.00')) {
            $value = str_replace('.00', '', $value);
        }

        return $value;
    }

    protected function cGPos(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->clearDotZeroes($this->attributes['cgpos']);
        });
    }

    protected function panel1Span(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->panels[1]['span'];
        });
    }

    protected function panel1Chord(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->panels[1]['chord'];
        });
    }

    protected function panel1Sweep(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->panels[1]['sweep'];
        });
    }

    protected function panel1Angle(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->panels[1]['angle'];
        });
    }

    protected function panel2Span(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->panels[2]['span'];
        });
    }

    protected function panel2Chord(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->panels[2]['chord'];
        });
    }

    protected function panel2Sweep(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->panels[2]['sweep'];
        });
    }

    protected function panel2Angle(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->panels[2]['angle'];
        });
    }

    protected function panel3Span(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->panels[3]['span'];
        });
    }

    protected function panel3Chord(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->panels[3]['chord'];
        });
    }

    protected function panel3Sweep(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->panels[3]['sweep'];
        });
    }

    protected function panel3Angle(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->panels[3]['angle'];
        });
    }

    protected function panel4Span(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->panels[4]['span'];
        });
    }

    protected function panel4Chord(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->panels[4]['chord'];
        });
    }

    protected function panel4Sweep(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->panels[4]['sweep'];
        });
    }

    protected function panel4Angle(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->panels[4]['angle'];
        });
    }

    protected function panel5Span(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->panels[5]['span'];
        });
    }

    protected function panel5Chord(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->panels[5]['chord'];
        });
    }

    protected function panel5Sweep(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->panels[5]['sweep'];
        });
    }

    protected function panel5Angle(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->panels[5]['angle'];
        });
    }

    protected function panel6Span(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->panels[6]['span'];
        });
    }

    protected function panel6Chord(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->panels[6]['chord'];
        });
    }

    protected function panel6Sweep(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->panels[6]['sweep'];
        });
    }

    protected function panel6Angle(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->panels[6]['angle'];
        });
    }

    /**
     * prepare the Panels array
     *
     * @return array return the Panels Array
     */
    private function prepPanelsArray()
    {
        $panels = $this->panels;
        if (gettype($panels) != 'array') {
            $panels = [];
        }
        for ($p = 1; $p <= 6; $p++) {
            if (! array_key_exists($p, $panels)) {
                $panels[$p] = [
                    'span' => 0.00,
                    'chord' => 0.00,
                    'sweep' => 0.00,
                    'angle' => 0.00];
            }
        }
        $this->attributes['panels'] = json_encode($panels);

        return $panels;
    }

    /**
     * A wing is owned by an user
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * A wing was liked by many users (many to many)
     *
     * @return BelongsToMany
     */
    public function likedBy()
    {
        return $this->belongsToMany('App\User', 'users_likes_wings');
    }

    /**
     * The attributes that should be casted to native types.
     */
    protected function casts(): array
    {
        return [
            'panels' => 'array',
        ];
    }
}
