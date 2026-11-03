@extends('layout')

@section('content')
    <div class="container">

        <h3>Login</h3>

        {{ html()->form('POST', url('/auth/login'))->open() }}

        <div class="form-group">
            {{ html()->label('Email', 'email') }}
            {{ html()->text('email')->class('form-control')->placeholder('Your email') }}
        </div>

        <div class="form-group">
            {{ html()->label('Password', 'password') }}
            {{ html()->password('password')->class('form-control') }}
        </div>

        <div class="form-group">
            {{ html()->checkbox('remember', true)->class('form-control') }} Remember me
        </div>

        {{ html()->submit('Login')->class('btn btn-primary') }}
        <a class="btn btn-danger" href="/password/email">Forgot your password?</a>
        </form>

        @include('errors.list')

    </div>
@stop
