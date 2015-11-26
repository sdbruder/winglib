<?php

namespace app;

use Illuminate\Database\Eloquent\Model;

class Wing extends Model
{

    protected $fillable = [
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
        'panel6angle'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'panels' => 'array'
    ];

    protected $appends = [
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
        'panel6angle'
    ];

    /**
     * boot method
     */
    protected static function boot()
    {
        parent::boot();

        // construct the panels json
        static::saving(function (Wing $wing) {
            $wing->panels = [
                '1' => ['span'  => $wing->attributes['panel1span'],
                        'chord' => $wing->attributes['panel1chord'],
                        'sweep' => $wing->attributes['panel1sweep'],
                        'angle' => $wing->attributes['panel1angle']],
                '2' => ['span'  => $wing->attributes['panel2span'],
                        'chord' => $wing->attributes['panel2chord'],
                        'sweep' => $wing->attributes['panel2sweep'],
                        'angle' => $wing->attributes['panel2angle']],
                '3' => ['span'  => $wing->attributes['panel3span'],
                        'chord' => $wing->attributes['panel3chord'],
                        'sweep' => $wing->attributes['panel3sweep'],
                        'angle' => $wing->attributes['panel3angle']],
                '4' => ['span'  => $wing->attributes['panel4span'],
                        'chord' => $wing->attributes['panel4chord'],
                        'sweep' => $wing->attributes['panel4sweep'],
                        'angle' => $wing->attributes['panel4angle']],
                '5' => ['span'  => $wing->attributes['panel5span'],
                        'chord' => $wing->attributes['panel5chord'],
                        'sweep' => $wing->attributes['panel5sweep'],
                        'angle' => $wing->attributes['panel5angle']],
                '6' => ['span'  => $wing->attributes['panel6span'],
                        'chord' => $wing->attributes['panel6chord'],
                        'sweep' => $wing->attributes['panel6sweep'],
                        'angle' => $wing->attributes['panel6angle']]
            ];
            if ( ! $wing->slug ) {
                $slug_str = str_slug($wing->name);
                $latest = Wing::whereRaw("slug_str = '{$slug_str}'")->latest("slug_id")->get()->first();
                if ($latest) {
                    $slug_id = $latest['slug_id'] + 1;
                    $wing->attributes['slug_str'] = $slug_str;
                    $wing->attributes['slug_id'] = $slug_id;
                    $wing->attributes['slug'] = $slug_str . '-' . $slug_id;
                } else {
                    $wing->attributes['slug_str'] = $slug_str;
                    $wing->attributes['slug'] = $slug_str;
                }
            }
            $wing->attributes['totalspan'] =
                ($wing->panels['1']['span'] +
                $wing->panels['2']['span'] +
                $wing->panels['3']['span'] +
                $wing->panels['4']['span'] +
                $wing->panels['5']['span'] +
                $wing->panels['6']['span']) * 2;

            foreach($wing->attributes as $key => $value) {
                if (in_array($key,$wing->appends)) {
                    unset($wing->attributes[$key]);
                }
            }

            return true;
        }); // static::saving
    }

    public static function createImage($base64_image) {
        define('URL_SEPARATOR', '/');

        $subdir = substr(microtime(),4,2);
        $storage_path = join(DIRECTORY_SEPARATOR, [storage_path(), 'app', 'wingimg', $subdir]);
        $url_path = join(URL_SEPARATOR, ['wingimg', $subdir]);
        $filename = uniqid('') . '.png';

        $image_filename = join(DIRECTORY_SEPARATOR, [ $storage_path, $filename]);
        $image_url      = URL_SEPARATOR . join(URL_SEPARATOR, [ $url_path, $filename]);

        //get the base-64 from data
        $base64_str = substr($base64_image, strpos($base64_image, ",")+1);
        //decode base64 string and write it down.
        $image = base64_decode($base64_str);
        file_put_contents($image_filename, $image);

        // return the URL made above.
        return $image_url;
    }

    public function saveImage($base64_image) {
        $image_filename = join(DIRECTORY_SEPARATOR, [ storage_path(), 'app']) . $this->image_file;
        //get the base-64 from data
        $base64_str = substr($base64_image, strpos($base64_image, ",")+1);
        //decode base64 string and write it down.
        file_put_contents($image_filename, base64_decode($base64_str));
    }

    public function endsWith($haystack, $needle) {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }

    public function clearDotZeroes($value) {
        if ($this->endsWith($value,'.00')) {
            $value = str_replace('.00', '', $value);
        }
        return $value;
    }

    public function getCGPosAttribute()
    {
        return $this->clearDotZeroes($this->attributes['cgpos']);
    }

    public function getPanel1SpanAttribute()
    {
        return $this->panels['1']['span'];
    }

    public function getPanel1ChordAttribute()
    {
        return $this->panels['1']['chord'];
    }

    public function getPanel1SweepAttribute()
    {
        return $this->panels['1']['sweep'];
    }

    public function getPanel1AngleAttribute()
    {
        return $this->panels['1']['angle'];
    }

    public function getPanel2SpanAttribute()
    {
        return $this->panels['2']['span'];
    }

    public function getPanel2ChordAttribute()
    {
        return $this->panels['2']['chord'];
    }

    public function getPanel2SweepAttribute()
    {
        return $this->panels['2']['sweep'];
    }

    public function getPanel2AngleAttribute()
    {
        return $this->panels['2']['angle'];
    }

    public function getPanel3SpanAttribute()
    {
        return $this->panels['3']['span'];
    }

    public function getPanel3ChordAttribute()
    {
        return $this->panels['3']['chord'];
    }

    public function getPanel3SweepAttribute()
    {
        return $this->panels['3']['sweep'];
    }

    public function getPanel3AngleAttribute()
    {
        return $this->panels['3']['angle'];
    }

    public function getPanel4SpanAttribute()
    {
        return $this->panels['4']['span'];
    }

    public function getPanel4ChordAttribute()
    {
        return $this->panels['4']['chord'];
    }

    public function getPanel4SweepAttribute()
    {
        return $this->panels['4']['sweep'];
    }

    public function getPanel4AngleAttribute()
    {
        return $this->panels['4']['angle'];
    }

    public function getPanel5SpanAttribute()
    {
        return $this->panels['5']['span'];
    }

    public function getPanel5ChordAttribute()
    {
        return $this->panels['5']['chord'];
    }

    public function getPanel5SweepAttribute()
    {
        return $this->panels['5']['sweep'];
    }

    public function getPanel5AngleAttribute()
    {
        return $this->panels['5']['angle'];
    }

    public function getPanel6SpanAttribute()
    {
        return $this->panels['6']['span'];
    }

    public function getPanel6ChordAttribute()
    {
        return $this->panels['6']['chord'];
    }

    public function getPanel6SweepAttribute()
    {
        return $this->panels['6']['sweep'];
    }

    public function getPanel6AngleAttribute()
    {
        return $this->panels['6']['angle'];
    }

    /**
     * prepare the Panels array
     * @return array return the Panels Array
     */
    private function prepPanelsArray()
    {
        $panels = $this->panels;
        if (gettype($panels) != 'array') {
            $panels = [];
        }
        for($p = 1; $p <= 6; $p++) {
            if (!array_key_exists($p, $panels)) {
                $panels[$p] = [
                    'span'  => 0.00,
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * A wing was liked by many users (many to many)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function likedBy()
    {
        return $this->belongsToMany('App\User', 'users_likes_wings');
    }
}
