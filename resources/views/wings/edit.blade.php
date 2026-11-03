@extends('layout')

@section('content')
    <div class="container">
        <h1>Wing edit: {{ $wing->name }}</h1>

        {{ html()->modelForm($wing, 'PUT', url('wings/'.$wing->id))->class('form-horizontal')->open() }}
        @include('wings.form', ['submitButton' => 'Update'])
        {{ html()->closeModelForm() }}

        @include('errors.list')

    </div>
@stop
