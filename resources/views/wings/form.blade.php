<input type="hidden" id="unitValue" name="unitValue" value="metric">
<input type="hidden" id="image_base64" name="image_base64">
<div class="row">
    <div class="col-md-10" id="canvasdiv">
        <canvas id="wingcanvas" width="940" height="400"></canvas>
    </div>
    <div class="col-md-2">
        <h4>Results</h4>
        <div class="form-group">
            {{ html()->label('Wing&nbsp;Area', 'wingarea')->class('col-sm-6 control-label') }}
            <div class="col-sm-6">
            {{ html()->text('wingarea')
                ->class('form-control input-sm')
                ->isReadonly()
                ->data('toggle', 'popover')
                ->data('trigger', 'hover')
                ->data('content', 'dm&sup2;')
                ->data('placement', 'left') }}
            </div>
        </div>
        <div class="form-group">
            {{ html()->label('MAC&nbsp;Dist', 'macdist')->class('col-sm-6 control-label') }}
            <div class="col-sm-6">
            {{ html()->text('macdist')
                ->class('form-control input-sm data-inmm')
                ->isReadonly()
                ->data('toggle', 'popover')
                ->data('trigger', 'hover')
                ->data('content', 'mm')
                ->data('placement', 'left') }}
            </div>
        </div>
        <div class="form-group">
            {{ html()->label('MAC&nbsp;Len', 'maclength')->class('col-sm-6 control-label') }}
            <div class="col-sm-6">
            {{ html()->text('maclength')
                ->class('form-control input-sm data-inmm')
                ->isReadonly()
                ->data('toggle', 'popover')
                ->data('trigger', 'hover')
                ->data('content', 'mm')
                ->data('placement', 'left') }}
            </div>
        </div>
        <div class="form-group">
            {{ html()->label('CG', 'cg')->class('col-sm-6 control-label') }}
            <div class="col-sm-6">
            {{ html()->text('cg')
                ->class('form-control input-sm data-inmm')
                ->isReadonly()
                ->data('toggle', 'popover')
                ->data('trigger', 'hover')
                ->data('content', 'mm')
                ->data('placement', 'left') }}
            </div>
        </div>
        <div class="form-group">
            {{ html()->label('Wing&nbsp;Load', 'wingload')->class('col-sm-6 control-label') }}
            <div class="col-sm-6">
            {{ html()->text('wingload')
                ->class('form-control input-sm')
                ->isReadonly()
                ->data('toggle', 'popover')
                ->data('trigger', 'hover')
                ->data('content', 'g/dm&sup2;')
                ->data('placement', 'left') }}
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <h4>Name and Description</h4>
        <div class="form-group">
            {{ html()->label('Wing&nbsp;Name', 'name')->class('col-sm-3 control-label') }}
            <div class="col-sm-9">
                {{ html()->text('name')->class('form-control')->placeholder('Wing name') }}
            </div>
        </div>
        <div class="form-group">
            {{ html()->label('Description', 'description')->class('col-sm-3 control-label') }}
            <div class="col-sm-9">
                {{-- Spatie renders a textarea value through its raw html() path, which would drop
                     the escaping the old builder applied to the model value. --}}
                <textarea name="description" id="description" rows="4" class="form-control"
                    placeholder="description / details of the wing">{{ old('description', $wing->description ?? null) }}</textarea>
            </div>
        </div>
        <div class="form-group">
            {{ html()->label('Draw', 'drawvalues')->class('col-sm-3 control-label') }}
            <div class="col-sm-9">
                <div class="checkbox">
                    <label>
                        {{ html()->checkbox('drawvalues', true)->class('redraw') }} Draw the measures.
                    </label>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <h4>Measures</h4>
        <div class="form-group">
            {{ html()->label('Unit&nbsp;System', 'unitsystem')->class('col-sm-3 control-label') }}
            <div class="col-sm-9">
                <div class="btn-group" data-toggle="buttons" id="UnitsystemRadios">
                    <label class="btn btn-primary" id="label_btn_metric">
                        {{ html()->radio('unitsystem', null, 'metric')->id('btn_metric') }} Metric
                    </label>
                    <label class="btn btn-primary" id="label_btn_imperial">
                        {{ html()->radio('unitsystem', null, 'imperial')->id('btn_imperial') }} Imperial
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group">
            {{ html()->label('CG&nbsp;Position', 'cgpos')->class('col-sm-3 control-label') }}
            <div class="col-sm-9">
                <div class="input-group">
                    {{ html()->text('cgpos')->class('form-control redraw') }}
                    <span class="input-group-addon nommin-addon" id="cgpos-addon">%</span>
                </div>
            </div>
        </div>
        <div class="form-group">
            {{ html()->label('Weight', 'weight')->class('col-sm-3 control-label') }}
            <div class="col-sm-9">
                <div class="input-group">
                    {{ html()->text('weight')->class('form-control') }}
                    <span class="input-group-addon nommin-addon" id="weight-addon">g</span>
                </div>
            </div>
        </div>
        <div class="form-group">
            {{ html()->label('Root Chord', 'rootchord')->class('col-sm-3 control-label') }}
            <div class="col-sm-9">
                <div class="input-group">
                    {{ html()->text('rootchord')->class('form-control redraw') }}
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
                {{ html()->label('Span', "panel{$p}span")->class('col-sm-3 control-label') }}
                <div class="col-sm-9">
                    <div class="input-group">
                        {{--  (($p==1) ? 600 : null)  --}}
                        {{ html()->text("panel{$p}span")
                            ->class('form-control redraw recalcangle')
                            ->attribute('panel', $p) }}
                        <span class="input-group-addon" id="panel{{$p}}span-addon">mm</span>
                    </div>
                </div>
            </div>
            <div class="form-group">
                {{ html()->label('Chord', "panel{$p}chord")->class('col-sm-3 control-label') }}
                <div class="col-sm-9">
                    <div class="input-group">
                        {{ html()->text("panel{$p}chord")->class('form-control redraw') }}
                        <span class="input-group-addon" id="panel{{$p}}chord-addon">mm</span>
                    </div>
                </div>
            </div>
            <div class="form-group">
                {{ html()->label('Sweep', "panel{$p}sweep")->class('col-sm-3 control-label') }}
                <div class="col-sm-9">
                    <div class="input-group">
                        {{ html()->text("panel{$p}sweep")
                            ->class('form-control redraw recalcangle')
                            ->attribute('panel', $p) }}
                        <span class="input-group-addon" id="panel{{$p}}sweep-addon">mm</span>
                    </div>
                </div>
            </div>
            <div class="form-group">
                {{ html()->label('Angle', "panel{$p}angle")->class('col-sm-3 control-label') }}
                <div class="col-sm-9">
                    <div class="input-group">
                        {{ html()->text("panel{$p}angle")
                            ->class('form-control redraw recalcsweep')
                            ->attribute('panel', $p) }}
                        <span class="input-group-addon nommin-addon" id="panel{{$p}}angle-addon">&deg;</span>
                    </div>
                </div>
            </div>
            </fieldset>
        </div>
    @endfor
</div>

{{ html()->submit($submitButton)->class('btn btn-primary form-control')->id('submit_button') }}

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
