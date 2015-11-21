<div class="row">
    <div class="col-md-6">
        <h4>Name and Description</h4>
        <div class="form-group">
            {!! Form::label('name', 'Wing&nbsp;Name', ['class' => 'col-sm-3 control-label']); !!}
            <div class="col-sm-9">
                {!! Form::text('name', null, ['class'=>'form-control', 'placeholder'=>'Wing name']);  !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('description', 'Description', ['class' => 'col-sm-3 control-label']);  !!}
            <div class="col-sm-9">
                {!! Form::textarea('description', null, [
                        'rows'        => '4',
                        'class'       => 'form-control',
                        'placeholder' => 'description / details of the wing'
                    ]);  !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('drawvalues', 'Draw', ['class' => 'col-sm-3 control-label']);  !!}
            <div class="col-sm-9">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('drawvalues');  !!} Draw the measures.
                    </label>
                </div>
            </div>
        </div>
        <h4>Measures</h4>
        <div class="form-group">
            {!! Form::label('unitsystem', 'Unit&nbsp;System', ['class' => 'col-sm-3 control-label']);  !!}
            <div class="col-sm-9">
                <div class="btn-group" data-toggle="buttons" id="UnitsystemRadios">
                    <label class="btn btn-primary">
                        {!! Form::radio('unitsystem', 'metric', true, []);  !!} Metric
                    </label>
                    <label class="btn btn-primary">
                        {!! Form::radio('unitsystem', 'imperial', false, []);  !!} Imperial
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('cgpos', 'CG&nbsp;Position', ['class' => 'col-sm-3 control-label']);  !!}
            <div class="col-sm-9">
                {!! Form::text('cgpos', null, ['class'=>'form-control']);  !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('weight', 'Weight', ['class' => 'col-sm-3 control-label']);  !!}
            <div class="col-sm-9">
                {!! Form::text('weight', null, ['class'=>'form-control']);  !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('rootchord', 'Root Chord', ['class' => 'col-sm-3 control-label']);  !!}
            <div class="col-sm-9">
                {!! Form::text('rootchord', null, ['class'=>'form-control']);  !!}
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <h4>Results</h4>

        <div class="form-group">
            {!! Form::label('wingarea', 'Wing&nbsp;Area', ['class' => 'col-sm-3 control-label']);  !!}
            <div class="col-sm-9">
            {!! Form::text('wingarea', null, ['class'=>'form-control']);  !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('macdist', 'MAC&nbsp;Distance', ['class' => 'col-sm-3 control-label']);  !!}
            <div class="col-sm-9">
            {!! Form::text('macdist', null, ['class'=>'form-control']);  !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('maclength', 'MAC&nbsp;Length', ['class' => 'col-sm-3 control-label']);  !!}
            <div class="col-sm-9">
            {!! Form::text('maclength', null, ['class'=>'form-control']);  !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('cg', 'CG', ['class' => 'col-sm-3 control-label']);  !!}
            <div class="col-sm-9">
            {!! Form::text('cg', null, ['class'=>'form-control']);  !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('wingload', 'Wing&nbsp;Load', ['class' => 'col-sm-3 control-label']);  !!}
            <div class="col-sm-9">
            {!! Form::text('wingload', null, ['class'=>'form-control']);  !!}
            </div>
        </div>
    </div>
</div>
<div class="row">
    @for ($p = 1; $p <= 6; $p++)
        <div class="col-md-6">
            <fieldset>
            <legend>Panel {{ $p }}</legend>
            <div class="form-group">
                {!! Form::label("panel{$p}span", 'Panel Span', ['class' => 'col-sm-3 control-label']);  !!}
                <div class="col-sm-9">
                    {!! Form::text("panel{$p}span", null, ['class'=>'form-control']);  !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label("panel{$p}chord", 'Chord', ['class' => 'col-sm-3 control-label']);  !!}
                <div class="col-sm-9">
                    {!! Form::text("panel{$p}chord", null, ['class'=>'form-control']);  !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label("panel{$p}sweep", 'Sweep', ['class' => 'col-sm-3 control-label']);  !!}
                <div class="col-sm-9">
                    {!! Form::text("panel{$p}sweep", null, ['class'=>'form-control']);  !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label("panel{$p}angle", 'Angle', ['class' => 'col-sm-3 control-label']);  !!}
                <div class="col-sm-9">
                    {!! Form::text("panel{$p}angle", null, ['class'=>'form-control']);  !!}
                </div>
            </div>
            </fieldset>
        </div>
    @endfor
</div>

{!! Form::submit($submitButton, ['class' => 'btn btn-primary form-control']); !!}

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#UnitsystemRadios label input').each(function( index ) {
                rdio = $(this).filter(':checked').val();
                if ( ! (rdio === undefined || rdio === null)) {
                    $(this).parent().addClass('active');
                }
            });
        });
    </script>
@stop
