@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <h1> Join a Course </h1>
                @if (isset($alpha_list))
                    <h4>Jump to professor's last name:</h4>
                    <h5>
                        @foreach ($alphabet as $a)
                            @if (in_array($a, $alphas))
                                <a href="#{{$a}}">{{$a}}</a>
                            @else
                                {{$a}}
                            @endif
                        @endforeach
                    </h5>
                    @foreach ($alpha_list as $a => $list)
                        <h2 id="{{$a}}">{{$a}}</h2>
                        <hr>
                        <div class="indent-1">
                            @foreach ($list as $l)
                                <h3 id="">{{$l["owner"]}}</h3>

                                <div class="list-group indent-2">
                                    @foreach ($l["courses"] as $class)
                                        <a class="list-group-item" href="join/{{$class->id}}">{{$class->name}}</a>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                @else
                    <div class="alert alert-info">No classes found</div>
                @endif

            </div>
        </div>
    </div>
@stop
