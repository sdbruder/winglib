<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Show the form that requests a reset link.
     *
     * The trait renders auth.passwords.email, a name this app has no template for - its own is
     * auth/password - so GET /password/reset answered 500 with "View [auth.passwords.email] not
     * found" on beta until this override. Line coverage never showed it: the view() call the trait
     * makes lives in vendor, while this controller's own lines were all executed.
     *
     * @return View
     */
    public function showLinkRequestForm()
    {
        return view('auth.password');
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }
}
