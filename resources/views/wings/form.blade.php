<div class="form-group">
    {!! Form::label('name', 'Wing Name');   !!}
    {!! Form::text('name', null,['class'=>'form-control', 'placeholder'=>'Wing name']);  !!}
</div>

{!! Form::submit($submitButton,['class' => 'btn btn-primary form-control']); !!}

