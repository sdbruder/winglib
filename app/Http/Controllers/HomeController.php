<?php

namespace App\Http\Controllers;

use App\Wing;
use DB;
use Illuminate\Http\Response;

class HomeController extends Controller
{
    /**
     * Home Page with Last, random and most voted wings
     *
     * @return Response
     */
    public function index()
    {
        $last = Wing::orderBy('updated_at', 'desc')->take(5)->get();

        $rand = Wing::orderByRaw('RAND()')->take(5)->get();

        $voted = Wing::leftJoin('users_likes_wings', 'users_likes_wings.wing_id', '=', 'wings.id')
            ->select(DB::raw('wings.*, count(users_likes_wings.wing_id) as likes'))      // 'wings.*', 'count(users_likes_wings.wing_id) as likes')
            ->groupBy('wings.id')
            ->orderBy('likes', 'desc')
            ->take(5)->get();

        return view('pages.home', [
            'last' => $last,
            'rand' => $rand,
            'voted' => $voted]);
    }
}
