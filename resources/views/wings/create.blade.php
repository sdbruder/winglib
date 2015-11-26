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

@section('javascript')
    <script type="text/javascript">
        // create javascript code
        $('#cgpos').val(20);
        $('#weight').val(500);
        $('#rootchord').val(340);
        $('#panel1span').val(600);
        $('#panel1chord').val(180);
        $('#panel1sweep').val(300);
        $('#panel1angle').val(26.57);
    </script>
@stop
