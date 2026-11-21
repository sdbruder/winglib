@extends('layout')

@section('content')
    <div class="container">

        <h1>Create your account</h1>

        {{ html()->form('POST', url('/auth/register'))->open() }}

        <div class="form-group">
            {{ html()->label('Name', 'name') }}
            {{ html()->text('name')->class('form-control')->placeholder('Your name') }}
        </div>
        <div class="form-group">
            {{ html()->label('Email', 'email') }}
            {{ html()->text('email')->class('form-control')->placeholder('Your email') }}
        </div>

        <div class="form-group">
            {{ html()->label('Password', 'password') }}
            {{ html()->password('password')->class('form-control') }}
        </div>

        <div class="form-group">
            {{ html()->label('Confirm Password', 'password_confirmation') }}
            {{ html()->password('password_confirmation')->class('form-control') }}
        </div>

        <div class="form-group">
            {{ html()->checkbox('remember', true)->class('form-control') }} Remember me
        </div>

        {{ html()->submit('Register')->class('btn btn-primary') }}
        </form>

        @include('errors.list')

    </div>
@stop
