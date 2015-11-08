<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Requests\WingRequest;

use Illuminate\Support\Facades\Auth;
use App\Wing;

class WingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $wings = Wing::all();
        return view('wings.index', compact('wings'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('wings.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\WingRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(WingRequest $request)
    {
        $wing = new Wing($request->all());
        Auth::user()->wings()->save($wing);

        return redirect('wings');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $wing = Wing::findOrFail($id);
        return view('wings.show', compact('wing'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $wing = Wing::findOrFail($id);
        return view('wings.edit', compact('wing'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\WingRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(WingRequest $request, $id)
    {
        $wing = Wing::findOrFail($id);

        $wing->update($request->all());

        return redirect('wings');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $wing = Wing::findOrFail($id);

        $wing->delete();

        return redirect('/wings');
    }
}
