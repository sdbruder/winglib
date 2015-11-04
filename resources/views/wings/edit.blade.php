@extends('layout')

@section('content')
    <div class="container">
        <h1>Wing edit: {{ $wing->name }}</h1>

        {!! Form::model($wing, ['method' => 'PUT','url' => 'wings/'.$wing->id]) !!}
        @include('wings.form', ['submitButton' => 'Update'])
        {!! Form::close(); !!}

        @include('errors.list')

    </div>
@stop
