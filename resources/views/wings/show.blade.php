@extends('layout')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-11">
                <h1>{{$wing->name}}
                    @if ((Auth::user()) and ($wing->user->id == Auth::user()->id))
                        <a href="/wings/{{$wing->id}}/edit" class="btn btn-primary">Edit</a>
                    @else
                        <div class="tooltip-wrapper"
                            data-toggle="popover"
                            data-trigger="hover"
                            data-content="You can't edit a wing that is not yours or you are not logged in"
                            data-placement="left">
                        <a href="/wings/{{$wing->id}}/edit" class="btn btn-primary disabled">Edit</a>
                        </div>
                    @endif
                    {{ html()->modelForm($wing, 'DELETE', url('wings/'.$wing->id))
                        ->class('form-inline')
                        ->style('display: inline')
                        ->open() }}
                    @if ((Auth::user()) and ($wing->user->id == Auth::user()->id))
                        {{ html()->submit('Delete')->class('btn btn-danger') }}
                    @else
                        <div class="tooltip-wrapper"
                            data-toggle="popover"
                            data-trigger="hover"
                            data-content="You can't delete a wing that is not yours or you are not logged in."
                            data-placement="bottom">
                        {{ html()->submit('Delete')->class('btn btn-danger disabled') }}
                        </div>
                    @endif
                    {{ html()->closeModelForm() }}
                    @if ((Auth::user()) and ($wing->user->id != Auth::user()->id))
                        <a href="/wings/{{$wing->id}}/forkit" class="btn btn-warning">Fork it!</a>
                    @else
                        <div class="tooltip-wrapper"
                            data-toggle="popover"
                            data-trigger="hover"
                            data-content="You can't fork wing that is yours or you are not logged in."
                            data-placement="right">
                        <a href="/wings/{{$wing->id}}/forkit" class="btn btn-warning disabled">Fork it!</a>
                        </div>
                    @endif
                </h1>
            </div>
            <div class="col-md-1">
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="media" onclick="document.location = '/wing/{{$wing->slug}}';">
                    <div class="media-body">
                        <div class="row">
                            <div class="col-md-12">
                            <h4>
                                by {{$wing->user->name}}
                                <a href=""
                                    data-toggle="popover"
                                    data-trigger="hover"
                                    data-content="{{$wing->user->name}}"
                                    data-placement="left"
                                >
                                <img src="{{$wing->user->avatar}}"
                                    alt="{{$wing->user->name}} "
                                    class="img-thumbnail img-circle"
                                    height="56" width="56">
                                </a>
                            </h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                {{$wing->description}}
                            </div>
                        </div>
                        <div class="row">
                            &nbsp;
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                            <b>Total Span:</b> {{$wing->totalspan}} {{ $wing->unit('distance') }}<br/>
                            <b>Wing Area:</b> {{$wing->wingarea}} {{ $wing->unit('area') }}<br/>
                            </div>
                        </div>
                        <div class="row">
                            &nbsp;
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                            <b>CG&commat;{{$wing->cgpos}}%:</b> {{$wing->cg}} {{ $wing->unit('distance') }}<br/>
                            <b>Wing Load:</b> {{$wing->wingload}} {{ $wing->unit('load') }}<br/>
                            </div>
                        </div>
                    </div>
                    <div class="media-right media-middle">
                        <img class="media-object" src="{{$wing->image_file}}" width="800px">
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                &nbsp;
            </div>
        </div>
        <div class="rows">
            <div class="col-md-12">
                <div id="disqus_thread"></div>
                <script>
                var disqus_config = function () {
                this.page.url = "{{ 'http://winglib.com/wing/'. $wing->slug }}";
                this.page.identifier = "{{ $wing->slug }}";
                };
                (function() { // DON'T EDIT BELOW THIS LINE
                var d = document, s = d.createElement('script');
                s.src = '//winglib.disqus.com/embed.js';
                s.setAttribute('data-timestamp', +new Date());
                (d.head || d.body).appendChild(s);
                })();
                </script>
                <noscript>Please enable JavaScript to view the <a href="https://disqus.com/?ref_noscript" rel="nofollow">comments powered by Disqus.</a></noscript>
            </div>
        </div>
    </div>
@stop
