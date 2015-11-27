@extends('layout')

@section('before-content')
    <div class="parallax">
        <div class="bg parallax-layer parallax-back">
        </div>
        <div class="container parallax-layer parallax-front">
            <div class="jumbotron">
                <h1>Wing Library</h1>
                <p class="lead">
                    Wing Library started as a flexible flying wing CG calculator for complex
                    wings with multiple panels, forward swept and configurable MAC and
                    CG positions and evolved to a public library for wing formats.
                </p>
                <div class="row">
                    <div class="col-md-6">
                    <a href="/wings/create" class="btn btn-lg btn-block btn-success">Create a wing</a>
                    </div>
                    <div class="col-md-6">
                    <a href="/wings/"       class="btn btn-lg btn-block btn-warning">Search the library</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container">
        <br/>
        {{--
        <p class="lead">
            All started with a need: calculate the CG of a complex (multiple
            panel) wing.
            Of that need I've created wingcgcalc, a flying wing CG calculator
            flexible enough to calculate complex wings, with multiple panels and
            forward swept. As the percentual position of the MAC for the CG is
            also configurable, you can use it with standard airplanes (with
            tail) too, It's only a question of configuring the right amount.
        </p>
        <hr>
        --}}
        <div class="row">
            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading"><h4>Last Wings
                    <span class="glyphicon glyphicon-step-forward" aria-hidden="true"></span>
                    </h4></div>
                    <div class="panel-body">
                    @foreach ($last as $w)
                        <div class="media" onclick="document.location = '/wing/{{$w->slug}}';">
                            <div class="media-left">
                                <a href="/wing/{{$w->slug}}">
                                    <img class="media-object" src="{{$w->image_file}}" width="90px">
                                </a>
                            </div>
                            <div class="media-body">
                                <h4 class="media-heading">{{$w->name}}</h4>
                                {{$w->description}}
                            </div>
                        </div>
                    @endforeach
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading"><h4>Most voted
                    <span class="glyphicon glyphicon-thumbs-up" aria-hidden="true"></span>
                    </h4></div>
                    <div class="panel-body">
                    @foreach ($voted as $w)
                        <div class="media" onclick="document.location = '/wing/{{$w->slug}}';">
                            <div class="media-left">
                                <a href="/wing/{{$w->slug}}">
                                    <img class="media-object" src="{{$w->image_file}}" width="90px">
                                </a>
                            </div>
                            <div class="media-body">
                                <h4 class="media-heading">{{$w->name}} ({{$w->likes}})</h4>
                                {{$w->description}}
                            </div>
                        </div>
                    @endforeach
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading"><h4>Random
                    <span class="glyphicon glyphicon-random" aria-hidden="true"></span>
                    </h4></div>
                    <div class="panel-body">
                    @foreach ($rand as $w)
                        <div class="media" onclick="document.location = '/wing/{{$w->slug}}';">
                            <div class="media-left">
                                <a href="/wing/{{$w->slug}}">
                                    <img class="media-object" src="{{$w->image_file}}" width="90px">
                                </a>
                            </div>
                            <div class="media-body">
                                <h4 class="media-heading">{{$w->name}}</h4>
                                {{$w->description}}
                            </div>
                        </div>
                    @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .parallax {
            perspective: 1px;
            height: 350px;
            overflow-x: hidden;
            overflow-y: auto;
        }
        .parallax-layer {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
        }
        .parallax-front {
            transform: translateZ(0);
        }
        .parallax-back {
            transform: translateZ(-1px) scale(2);
        }
        .bg {
            background: url('/img/flyingwing-parallax.jpg') repeat center;
            /*top: -250px;*/
            /*background: url('/img/flyingwing-nasa.jpg') no-repeat center center;*/
            /*height: 350px;*/
/*            position: fixed;
            width: 100%;
            height: 350px; same height as jumbotron
            top: 50px;
            left:0;
            z-index: -1;*/
        }
        .jumbotron {
            /*height: 350px;*/
            background:transparent;
        }
        .jumbotron h1, .jumbotron p {
            color: white;
            text-shadow: #000 0px 2px 2px;
        }
    </style>
@stop

@section('javascript')
    <script>
        $(document).ready(function() {
            var jumboHeight = $('.jumbotron').outerHeight();

            function parallax(){
                $('.bg').css('height', (jumboHeight-$(window).scrollTop()) + 'px');
            }

            // $(window).scroll(function(e){
            //     window.requestAnimationFrame(parallax);
            //     // parallax();
            // });
        });
    </script>
@stop
