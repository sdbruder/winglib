@extends('layout')

@section('content')
    <div class="starter-template">
        <h1>Wing Library</h1>


        <table class="table table-hover">
        <thead>
            <tr>
                <th>Wing</th>
                <th width="60px">Likes</th>
                <th width="60px">Author</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($wings as $w)
                <tr>
                    <th>
                        <div class="media" onclick="document.location = '/wing/{{$w->slug}}';">
                            <div class="media-left media-middle">
                                <a href="#">
                                    <img class="media-object" src="{{$w->imageVariant(282)}}" width="141" height="60">
                                </a>
                            </div>
                            <div class="media-body row">
                                <div class="col-md-6">
                                <h4 class="media-heading">{{$w->name}}</h4>
                                {{$w->description}}
                                </div>
                                <div class="col-md-3">
                                <b>Total Span:</b> {{$w->totalspan}} {{$w->unit('distance')}}<br/>
                                <b>Wing Area:</b> {{$w->wingarea}} {{$w->unit('area')}}<br/>
                                </div>
                                <div class="col-md-3">
                                <b>CG&commat;{{$w->cgpos}}%:</b> {{$w->cg}} {{$w->unit('distance')}}<br/>
                                <b>Wing Load:</b> {{$w->wingload}} {{$w->unit('load')}}<br/>
                                </div>
                            </div>
                        </div>
                    </th>
                    <th width="60px">
                        @if ($w->likedby->count() == 0)
                            <span data-toggle="ajax-likeit" data-id="{{$w->id}}">
                                <span class="glyphicon glyphicon-thumbs-up" aria-hidden="true"></span>
                            </span>
                        @else
                            <span data-toggle="ajax-likeit" data-id="{{$w->id}}">
                                {{ $w->likedby->count() }} <span class="glyphicon glyphicon-thumbs-up" aria-hidden="true"></span>
                            </span>
                        @endif
                    </th>
                    <th width="60px">
                        <a href=""
                            data-toggle="popover"
                            data-trigger="hover"
                            data-content="{{$w->user->name}}"
                            data-placement="left"
                        >
                        <img src="{{$w->user->avatar}}"
                            alt="{{$w->user->name}} "
                            class="img-thumbnail img-circle"
                            height="56" width="56">
                        </a>
                    </th>
                </tr>
            @endforeach
        </tbody>
        </table>

        {!! $wings->render() !!}

    </div>
@stop

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $('[data-toggle="ajax-likeit"]').click(function (event) {
                data = $(this).data('id');
                $.ajax({
                    type: 'POST',
                    url: '/wings/likeit',
                    context: $(this),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: { id: data },
                    dataType: 'json',
                    success: function(response) {
                        $( this).html( response.toString() + ' <span class="glyphicon glyphicon-thumbs-up" aria-hidden="true"></span>' );
                    },
                    error: function(xhr,textStatus, e) {
                        console.log(textStatus);
                    }
                })
            });
        });
    </script>
@stop

{{--
            type: "POST",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        cache: false,
        dataType: "json",
        error: function(e) {
            console.log(e.responseText);
        }
--}}
