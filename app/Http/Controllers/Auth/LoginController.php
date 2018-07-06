<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    /**
     * Redirect the user to the Facebook authentication page.
     *
     * @return Response
     */
    public function redirectToProvider($driver)
    {
        if (!config("services.$driver")) {
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
                    $tokenSecret = null;
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
