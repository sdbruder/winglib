<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Validator;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    protected $redirectTo = '/';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'getLogout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }

    /**
     * Redirect the user to the Facebook authentication page.
     *
     * @return Response
     */
    public function redirectToProvider($driver)
    {
        if(!config("services.$driver")) {
            abort('404'); //just to handle providers that doesn't exist
        }

        return Socialite::driver($driver)->redirect();
    }

    /**
     * Obtain the user information from Facebook.
     *
     * @return Response
     */
    public function handleProviderCallback($driver)
    {
        $u = Socialite::driver($driver)->user();
        Log::info('Socialite user', ['provider'=> $driver, 'user' => (array) $u ]);
        if ($u) {
            $user = User::where('provider_id', '=', $u->id)->first();
            if (!$user) {
                if (array_key_exists('tokenSecret', (array) $u)) {
                    $tokenSecret = $u->tokenSecret;
                } else {
                    $tokenSecret = NULL;
                }
                $user = User::create([
                    'name'         => $u->name,
                    'username'     => $u->nickname,
                    'email'        => $u->email,
                    'avatar'       => $u->avatar,
                    'provider'     => $driver,
                    'provider_id'  => $u->id,
                    'token'        => $u->token,
                    'token_secret' => $tokenSecret
                ]);
                Log::info('Created new user.', ['provider'=> $driver, 'user' => (array) $u ]);
            } else {
                Log::info('Login.', ['provider'=> $driver, 'user' => (array) $user ]);
            }
            Auth::login($user, true);

            return redirect('/');
        } else {
            abort('404');
        }

    }
}
