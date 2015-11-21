@extends('layout')

@section('content')
    <div class="container">
        <h1>Wing create</h1>

        {!! Form::open(['url' => 'wings', 'class' => 'form-horizontal']); !!}
        @include('wings.form', ['submitButton' => 'Create'])
        {!! Form::close(); !!}

        @include('errors.list')

    </div>
@stop
