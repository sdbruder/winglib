@extends('layout')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-11">
                <h1>Wing #{{$wing->id}}
                    <a href="/wings/{{$wing->id}}/edit" class="btn btn-primary">Edit</a>
                    {!! Form::model($wing, [
                        'method' => 'DELETE',
                        'url'    => 'wings/'.$wing->id,
                        'class'  => 'form-inline',
                        'style'  => 'display: inline'
                    ]) !!}
                    {!! Form::submit('Delete',['class' => 'btn btn-danger']); !!}
                    {!! Form::close(); !!}
                </h1>
            </div>
            <div class="col-md-1">
            </div>
        </div>
        <table class="table table-condensed table-hover">
        <thead>
            <tr>
            <th>Title</th>
            </tr>
        </thead>
        <tbody>
                <tr>
                <th>{{$wing->name}}</th>
                 </tr>
        </tbody>
        </table>
    </div>
@stop
