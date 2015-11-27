@extends('layout')

@section('content')
    <div class="container">
        <h3>Password</h3>
        <div class="row">
            <div class="col-md-6">
                {!! Form::open(array('url' => '/password/reset')); !!}
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="token" value="{{ $token }}">
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

                {!! Form::submit('Reset password',['class' => 'btn btn-primary']); !!}
                {!! Form::close(); !!}

                @include('errors.list')
            </div>
        </div>
    </div>
@stop
