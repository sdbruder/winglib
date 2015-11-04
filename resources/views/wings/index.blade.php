@extends('layout')

@section('content')
    <div class="starter-template">
        <h1>Wing Library</h1>

        <table class="table table-condensed table-hover">
        <thead>
            <tr>
            <th>Id</th>
            <th>Title</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($wings as $w)
            <tr onclick="document.location = '/wings/{{$w->id}}';">
                <th><b><a href="/wings/{{$w->id}}">{{$w->id}}</a></b></th>
                <th>{{$w->name}}</th>
            </tr>
            @endforeach
        </tbody>
        </table>
    </div>
@stop
