@extends('layout')

@section('content')
    <div class="container">
        <h3>Password</h3>
        <div class="row">
            <div class="col-md-6">
                {{ html()->form('POST', url('/password/reset'))->open() }}
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="token" value="{{ $token }}">
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

                {{ html()->submit('Reset password')->class('btn btn-primary') }}
                </form>

                @include('errors.list')
            </div>
        </div>
    </div>
@stop
