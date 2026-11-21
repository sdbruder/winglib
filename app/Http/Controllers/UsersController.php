<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class UsersController extends Controller
{
    /**
     * User settings
     *
     * @return Response
     */
    public function settings()
    {
        return view('users.settings');
    }
}
