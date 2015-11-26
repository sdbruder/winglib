<input type="hidden" id="unitValue" name="unitValue" value="metric">
<input type="hidden" id="image_base64" name="image_base64">
<div class="row">
    <div class="col-md-10" id="canvasdiv">
        <canvas id="wingcanvas" width="940" height="400"></canvas>
    </div>
    <div class="col-md-2">
        <h4>Results</h4>
        <div class="form-group">
            {!! Form::label('wingarea', 'Wing&nbsp;Area', ['class' => 'col-sm-6 control-label']);  !!}
            <div class="col-sm-6">
            {!! Form::text('wingarea', null, [
                'class'          => 'form-control input-sm',
                'readonly'       => '',
                'data-toggle'    => 'popover',
                'data-trigger'   => 'hover',
                'data-content'   => 'dm&sup2;',
                'data-placement' => 'left'
                ]);  !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('macdist', 'MAC&nbsp;Dist', ['class' => 'col-sm-6 control-label']);  !!}
            <div class="col-sm-6">
            {!! Form::text('macdist', null, [
                'class'          => 'form-control input-sm data-inmm',
                'readonly'       => '',
                'data-toggle'    => 'popover',
                'data-trigger'   => 'hover',
                'data-content'   => 'mm',
                'data-placement' => 'left'
                ]);  !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('maclength', 'MAC&nbsp;Len', ['class' => 'col-sm-6 control-label']);  !!}
            <div class="col-sm-6">
            {!! Form::text('maclength', null, [
                'class'          => 'form-control input-sm data-inmm',
                'readonly'       => '',
                'data-toggle'    => 'popover',
                'data-trigger'   => 'hover',
                'data-content'   => 'mm',
                'data-placement' => 'left'
                ]);  !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('cg', 'CG', ['class' => 'col-sm-6 control-label']);  !!}
            <div class="col-sm-6">
            {!! Form::text('cg', null, [
                'class'          => 'form-control input-sm data-inmm',
                'readonly'       => '',
                'data-toggle'    => 'popover',
                'data-trigger'   => 'hover',
                'data-content'   => 'mm',
                'data-placement' => 'left'
                ]);  !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('wingload', 'Wing&nbsp;Load', ['class' => 'col-sm-6 control-label']);  !!}
            <div class="col-sm-6">
            {!! Form::text('wingload', null, [
                'class'          => 'form-control input-sm',
                'readonly'       => '',
                'data-toggle'    => 'popover',
                'data-trigger'   => 'hover',
                'data-content'   => 'g/dm&sup2;',
                'data-placement' => 'left'
                ]);  !!}
            </div>
        </div>
    </div>
</div>
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
                        {!! Form::checkbox('drawvalues', Null, Null, [
                            'class' => 'redraw',
                            'checked' => 'Checked']);  !!} Draw the measures.
                    </label>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <h4>Measures</h4>
        <div class="form-group">
            {!! Form::label('unitsystem', 'Unit&nbsp;System', ['class' => 'col-sm-3 control-label']);  !!}
            <div class="col-sm-9">
                <div class="btn-group" data-toggle="buttons" id="UnitsystemRadios">
                    <label class="btn btn-primary" id="label_btn_metric">
                        {!! Form::radio('unitsystem', 'metric', false, ['id' => 'btn_metric']); !!} Metric
                    </label>
                    <label class="btn btn-primary" id="label_btn_imperial">
                        {!! Form::radio('unitsystem', 'imperial', false, ['id' => 'btn_imperial']); !!} Imperial
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('cgpos', 'CG&nbsp;Position', ['class' => 'col-sm-3 control-label']);  !!}
            <div class="col-sm-9">
                <div class="input-group">
                    {!! Form::text('cgpos', null, ['class'=>'form-control redraw']);  !!}
                    <span class="input-group-addon nommin-addon" id="cgpos-addon">%</span>
                </div>
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('weight', 'Weight', ['class' => 'col-sm-3 control-label']);  !!}
            <div class="col-sm-9">
                <div class="input-group">
                    {!! Form::text('weight', null, ['class'=>'form-control']);  !!}
                    <span class="input-group-addon nommin-addon" id="weight-addon">g</span>
                </div>
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('rootchord', 'Root Chord', ['class' => 'col-sm-3 control-label']);  !!}
            <div class="col-sm-9">
                <div class="input-group">
                    {!! Form::text('rootchord', null, ['class'=>'form-control redraw']);  !!}
                    <span class="input-group-addon" id="rootchord-addon">mm</span>
                </div>
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
                {!! Form::label("panel{$p}span", 'Span', ['class' => 'col-sm-3 control-label']);  !!}
                <div class="col-sm-9">
                    <div class="input-group">
                        {{--  (($p==1) ? 600 : null)  --}}
                        {!! Form::text("panel{$p}span", null, [
                            'class'=>'form-control redraw']);  !!}
                        <span class="input-group-addon" id="panel{{$p}}span-addon">mm</span>
                    </div>
                </div>
            </div>
            <div class="form-group">
                {!! Form::label("panel{$p}chord", 'Chord', ['class' => 'col-sm-3 control-label']);  !!}
                <div class="col-sm-9">
                    <div class="input-group">
                        {!! Form::text("panel{$p}chord", null, [
                            'class'=>'form-control redraw']);  !!}
                        <span class="input-group-addon" id="panel{{$p}}chord-addon">mm</span>
                    </div>
                </div>
            </div>
            <div class="form-group">
                {!! Form::label("panel{$p}sweep", 'Sweep', ['class' => 'col-sm-3 control-label']);  !!}
                <div class="col-sm-9">
                    <div class="input-group">
                        {!! Form::text("panel{$p}sweep", null, [
                            'class' => 'form-control redraw recalcangle',
                            'panel' => $p ]);  !!}
                        <span class="input-group-addon" id="panel{{$p}}sweep-addon">mm</span>
                    </div>
                </div>
            </div>
            <div class="form-group">
                {!! Form::label("panel{$p}angle", 'Angle', ['class' => 'col-sm-3 control-label']);  !!}
                <div class="col-sm-9">
                    <div class="input-group">
                        {!! Form::text("panel{$p}angle", null, [
                            'class' => 'form-control redraw recalcsweep',
                            'panel' => $p ]);  !!}
                        <span class="input-group-addon nommin-addon" id="panel{{$p}}angle-addon">&deg;</span>
                    </div>
                </div>
            </div>
            </fieldset>
        </div>
    @endfor
</div>

{!! Form::submit($submitButton, ['class' => 'btn btn-primary form-control', 'id' => 'submit_button']); !!}

@section('javascript')
@parent
    <script src="/js/winglib.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            wingcgcalc_setup();
            draw_wing();
            $('#name').focus();
        });
    </script>
@stop
