<?php

namespace App\Http\Controllers;

use App\Http\Requests\WingRequest;
use App\Wing;
use Flash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class WingsController extends Controller
{
    /**
     * Fork a wing already registered making a copy of it for the current user.
     *
     * @param  string  $id
     * @return Response
     */
    public function forkIt($id)
    {
        $origWing = Wing::findOrFail($id);
        if ((Auth::user()) and ($origWing->user->id != Auth::user()->id)) {
            $newWing = $origWing->replicate();
            // <UglyStuff>
            $newWing->slug = null; // We want a new slug, so let it be created again.
            $newWing->image_file = Wing::duplicateImage($origWing->image_file);
            $newWing->name .= ' a forked version';
            // </UglyStuff>
            Auth::user()->wings()->save($newWing);

            return redirect('/wings/'.$newWing->id.'/edit');
        } else {
            Flash::error('You need to be logged in and<br/>you can only fork a wing that isn\'t yours.');

            return redirect()->back();
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $wings = Wing::paginate(10);

        return view('wings.index', compact('wings'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('wings.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(WingRequest $request)
    {
        if (Auth::user()) {
            // saves the image and return the URL for it.
            $image_url = Wing::createImage($request->image_base64);
            $wing = new Wing($request->all());
            $wing->image_file = $image_url;
            // create the wing
            Auth::user()->wings()->save($wing);

            return redirect('wings');
        } else {
            Flash::error('You cant save a wing without a user.<br/>'.
                'Login or create an account to be able to do it.');

            return redirect()->back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $wing = Wing::findOrFail($id);

        return view('wings.show', compact('wing'));
    }

    /**
     * Display the specified resource by the slug
     *
     * @param  string  $slug
     * @return Response
     */
    public function showSlug($slug)
    {
        $wing = Wing::where('slug', $slug)->firstorfail();

        return view('wings.show', compact('wing'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $wing = Wing::findOrFail($id);
        if ((Auth::user()) and ($wing->user->id == Auth::user()->id)) {
            return view('wings.edit', compact('wing'));
        } else {
            Flash::warning('This wing is not yours or you are not logged in.<br/>'.
                'You cannot edit it.');

            return redirect()->back();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(WingRequest $request, $id)
    {
        $wing = Wing::findOrFail($id);

        // saves the image using the URL in the Model.
        $wing->saveImage($request->image_base64);

        $wing->update($request->all());

        return redirect('wings');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $wing = Wing::findOrFail($id);

        if ((Auth::user()) and ($wing->user->id == Auth::user()->id)) {
            $wing->delete();

            return redirect('/wings');
        } else {
            Flash::error('This wing is not yours or you are not logged in.<br/>'.
                'You cannot delete it.');

            return redirect()->back();
        }
    }

    /**
     * Ajax Like it: insert / delete a relationship in users_likes_wings
     *
     * @return JsonResponse
     */
    public function likeit(Request $request)
    {
        $wing = Wing::findOrFail($request->input('id'));
        $user_id = Auth::id();

        $count = $wing->likedby->count();
        if (! $wing->likedby->contains($user_id)) {
            $wing->likedby()->attach($user_id);
            $count += 1;
        }

        return response()->json($count);
    }
}
