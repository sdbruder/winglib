@extends('layout')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-11">
                <h1>{{$wing->name}}
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

        <div class="media" onclick="document.location = '/wing/{{$wing->slug}}';">
            <div class="media-body">
                <div class="row">
                    <div class="col-md-12">
                        {{$wing->description}}
                    </div>
                </div>
                <div class="row">
                    &nbsp;
                </div>
                <div class="row">
                    <div class="col-md-12">
                    <b>Total Span:</b> {{$wing->totalspan}} {{ $wing->unit('distance') }}<br/>
                    <b>Wing Area:</b> {{$wing->wingarea}} {{ $wing->unit('area') }}<br/>
                    </div>
                </div>
                <div class="row">
                    &nbsp;
                </div>
                <div class="row">
                    <div class="col-md-12">
                    <b>CG&commat;{{$wing->cgpos}}%:</b> {{$wing->cg}} {{ $wing->unit('distance') }}<br/>
                    <b>Wing Load:</b> {{$wing->wingload}} {{ $wing->unit('load') }}<br/>
                    </div>
                </div>
            </div>
            <div class="media-right media-middle">
                <img class="media-object" src="{{$wing->image_file}}" width="800px">
            </div>
        </div>


{{--        <table class="table table-condensed table-hover">
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
    </div> --}}
@stop
