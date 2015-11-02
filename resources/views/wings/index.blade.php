@extends('layout')

@section('content')
    <div class="starter-template">
        <h1>Wing Library</h1>
        <p class="lead">Use this document as a way to quickly start any new project.<br>
        All you get is this text and a mostly barebones HTML document.</p>

        <table class="table table-condensed table-hover">
        <thead>
            <tr>
            <th>Id</th>
            <th>Title</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($wings as $w)
            <tr>
                <th><b>{{$w->id}}</b></th>
                <th>{{$w->name}}</th>
            </tr>
            @endforeach
        </tbody>
        </table>
    </div>
@stop
