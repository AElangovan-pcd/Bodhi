@extends('layouts.instructor')

@section('links')
    <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
    <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/review/landing')}}">Peer Review</a></li>
    <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/review/'.$assignment->id.'/monitor')}}">Monitor</a></li>
@stop

@section('JS')
    <script src="{{ asset('js/ractive.min.js') }}"></script>
@endsection

@section('content')
    <div id="target"></div>

    <script id="template" type="text/ractive">
        @include('instructor.review.statsPage')
    </script>

    <script type="text/javascript">

        var data = {!! $data !!};
        console.log(data);

        var ractive = new Ractive({
            el: "#target",
            template: "#template",
            data: data,
            delimiters: [ '[[', ']]' ],
        });
    </script>
@endsection
