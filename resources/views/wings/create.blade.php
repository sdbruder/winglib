@extends('layout')

@section('content')
    <div class="starter-template">
        <h1>Wing create</h1>
        <p class="lead">Use this document as a way to quickly start any new project.<br>
        All you get is this text and a mostly barebones HTML document.</p>

        {!! Form::open(array('url' => 'wings')) !!}

        <div class="form-group">
            {!! Form::label('name', 'Wing Name');   !!}
            {!! Form::text('name', null,['class'=>'form-control', 'placeholder'=>'Wing name']);  !!}
        </div>

        {!! Form::submit('Create',['class' => 'btn btn-primary form-control']); !!}

        {!! Form::close(); !!}

    </div>
@stop
