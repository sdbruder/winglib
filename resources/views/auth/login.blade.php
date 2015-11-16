@extends('layout')

@section('content')
    <div class="container">

        <h3>Login</h3>

        {!! Form::open(array('url' => '/auth/login')); !!}

        <div class="form-group">
            {!! Form::label('email', 'Email'); !!}
            {!! Form::text('email', old('email'), ['class'=>'form-control', 'placeholder'=>'Your email']);  !!}
        </div>

        <div class="form-group">
            {!! Form::label('password', 'Password'); !!}
            {!! Form::password('password', ['class'=>'form-control' ]);  !!}
        </div>

        <div class="form-group">
            {!! Form::checkbox('remember', null, ['class'=>'form-control']); !!} Remember me
        </div>

        {!! Form::submit('Login',['class' => 'btn btn-primary']); !!}
        {!! Form::close(); !!}

        @include('errors.list')

    </div>
@stop
