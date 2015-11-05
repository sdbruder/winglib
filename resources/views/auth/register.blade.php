@extends('layout')

@section('content')
    <div class="container">

        <h1>Create your account</h1>

        {!! Form::open(array('url' => '/auth/register')); !!}

        <div class="form-group">
            {!! Form::label('name', 'Name'); !!}
            {!! Form::text('name', old('name'), ['class'=>'form-control', 'placeholder'=>'Your name']);  !!}
        </div>
        <div class="form-group">
            {!! Form::label('email', 'Email'); !!}
            {!! Form::text('email', old('email'), ['class'=>'form-control', 'placeholder'=>'Your email']);  !!}
        </div>

        <div class="form-group">
            {!! Form::label('password', 'Password'); !!}
            {!! Form::password('password', ['class'=>'form-control' ]);  !!}
        </div>

        <div class="form-group">
            {!! Form::label('password_confirmation', 'Confirm Password'); !!}
            {!! Form::password('password_confirmation', ['class'=>'form-control' ]);  !!}
        </div>

        <div class="form-group">
            {!! Form::checkbox('remember', null, ['class'=>'form-control']); !!} Remember me
        </div>

        {!! Form::submit('Register',['class' => 'btn btn-primary']); !!}
        {!! Form::close(); !!}

        @include('errors.list')

    </div>
@stop
