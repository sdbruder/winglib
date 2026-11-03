@extends('layout')

@section('content')
    <div class="container">

        <h3>Password reset</h3>

        <div class="row">
            <div class="col-md-6">
                {{ html()->form('POST', url('/password/email'))->open() }}

                <div class="form-group">
                    {{ html()->label('Email', 'email') }}
                    {{ html()->text('email')->class('form-control')->placeholder('Your email') }}
                </div>

                {{ html()->submit('Send password reset link')->class('btn btn-primary') }}
                </form>

                @include('errors.list')
            </div>
        </div>

    </div>
@stop
