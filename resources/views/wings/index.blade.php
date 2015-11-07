@extends('layout')

@section('content')
    <div class="starter-template">
        <h1>Wing Library</h1>


        <table class="table table-hover">
        <thead>
            <tr>
                <th>Wing</th>
                <th width="60px">Author</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($wings as $w)
                <tr onclick="document.location = '/wings/{{$w->id}}';">
                    <th>
                        <div class="media">
                            <div class="media-left media-middle">
                                <a href="#">
                                    <img class="media-object" src="/img/wing.png" height="60px">
                                </a>
                            </div>
                            <div class="media-body">
                                <h4 class="media-heading">{{$w->name}}</h4>

                                Nullam id dolor id nibh ultricies vehicula ut id
                                elit. Cras justo odio, dapibus ac facilisis in,
                                egestas eget quam. Lorem ipsum dolor sit amet,
                                consectetur adipiscing elit. Nullam quis risus
                                eget urna mollis ornare vel eu leo.

                            </div>
                        </div>
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


        <table class="table table-hover">
        <thead>
            <tr>
                <th width="100px">Wing</th>
                <th>Description</th>
                <th width="60px">Author</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($wings as $w)
                <tr onclick="document.location = '/wings/{{$w->id}}';">
                    <th width="100px">
                        <img src="/img/wing.png"
                            height="60">
                        <!-- <b><a href="/wings/{{$w->id}}">{{$w->id}}</a></b> -->
                    </th>
                    <th>{{$w->name}}</th>
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

        <table class="table table-condensed table-hover">
        <thead>
            <tr>
            <th>Id</th>
            <th>Title</th>
            <th>User</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($wings as $w)
                <tr onclick="document.location = '/wings/{{$w->id}}';">
                    <th width="100px">
                        <img src="/img/wing.png"
                            height="60">
                        <!-- <b><a href="/wings/{{$w->id}}">{{$w->id}}</a></b> -->
                    </th>
                    <th>{{$w->name}}</th>
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

    </div>
@stop
