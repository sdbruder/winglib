@extends('layout')

@section('content')
    <div class="container">
        <h1>Wing #{{$wing->id}}</h1>
        <p class="lead">Use this document as a way to quickly start any new project.<br>
        All you get is this text and a mostly barebones HTML document.</p>

        <table class="table table-condensed table-hover">
        <thead>
            <tr>
            <th>Title</th>
            </tr>
        </thead>
        <tbody>
                <tr>
                <th>{{$wing->title}}</th>
                 </tr>
        </tbody>
        </table>
    </div>
@stop
