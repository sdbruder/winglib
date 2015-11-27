@extends('layout')

@section('content')
    <div class="container">

        <h3>Password reset</h3>

        <div class="row">
            <div class="col-md-6">
                {!! Form::open(array('url' => '/password/email')); !!}

                <div class="form-group">
                    {!! Form::label('email', 'Email'); !!}
                    {!! Form::text('email', old('email'), ['class'=>'form-control', 'placeholder'=>'Your email']);  !!}
                </div>

                {!! Form::submit('Send password reset link',['class' => 'btn btn-primary']); !!}
                {!! Form::close(); !!}

                @include('errors.list')
            </div>
        </div>

    </div>
@stop
