<?php
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace app{
/**
 * App\User
 *
 * @property int $id
 * @property string $name
 * @property string|null $username
 * @property string|null $email
 * @property string $avatar
 * @property string $provider
 * @property string $provider_id
 * @property string $token
 * @property string|null $token_secret
 * @property string $password
 * @property string|null $remember_token
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\app\Wing[] $likes
 * @property-read \Illuminate\Database\Eloquent\Collection|\app\Wing[] $wings
 * @method static \Illuminate\Database\Eloquent\Builder|\app\User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\User whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\User whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\User whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\User whereTokenSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\User whereUsername($value)
 */
	class User extends \Eloquent {}
}

namespace app{
/**
 * App\Wing
 *
 * @property int $id
 * @property int $user_id
 * @property string $slug
 * @property string $slug_str
 * @property int $slug_id
 * @property string $name
 * @property string $description
 * @property string $image_file
 * @property string $unitsystem
 * @property float $cgpos
 * @property float $weight
 * @property int $drawvalues
 * @property float $rootchord
 * @property float $totalspan
 * @property array $panels
 * @property float $wingarea
 * @property float $macdist
 * @property float $maclength
 * @property float $cg
 * @property float $wingload
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read mixed $c_g_pos
 * @property-read mixed $panel1_angle
 * @property-read mixed $panel1_chord
 * @property-read mixed $panel1_span
 * @property-read mixed $panel1_sweep
 * @property-read mixed $panel2_angle
 * @property-read mixed $panel2_chord
 * @property-read mixed $panel2_span
 * @property-read mixed $panel2_sweep
 * @property-read mixed $panel3_angle
 * @property-read mixed $panel3_chord
 * @property-read mixed $panel3_span
 * @property-read mixed $panel3_sweep
 * @property-read mixed $panel4_angle
 * @property-read mixed $panel4_chord
 * @property-read mixed $panel4_span
 * @property-read mixed $panel4_sweep
 * @property-read mixed $panel5_angle
 * @property-read mixed $panel5_chord
 * @property-read mixed $panel5_span
 * @property-read mixed $panel5_sweep
 * @property-read mixed $panel6_angle
 * @property-read mixed $panel6_chord
 * @property-read mixed $panel6_span
 * @property-read mixed $panel6_sweep
 * @property-read \Illuminate\Database\Eloquent\Collection|\app\User[] $likedBy
 * @property-read \app\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\app\Wing whereCg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\Wing whereCgpos($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\Wing whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\Wing whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\Wing whereDrawvalues($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\Wing whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\Wing whereImageFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\Wing whereMacdist($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\Wing whereMaclength($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\Wing whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\Wing wherePanels($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\Wing whereRootchord($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\Wing whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\Wing whereSlugId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\Wing whereSlugStr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\Wing whereTotalspan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\Wing whereUnitsystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\Wing whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\Wing whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\Wing whereWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\Wing whereWingarea($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\app\Wing whereWingload($value)
 */
	class Wing extends \Eloquent {}
}

