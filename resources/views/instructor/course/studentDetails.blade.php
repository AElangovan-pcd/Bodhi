@extends('layouts.instructor')

@section('JS')
    <script src="{{ asset('js/ractive.min.js') }}"></script>
    <script src="{{ asset('js/papaparse.min.js') }}"></script>
@endsection

@section('links')
    <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
@endsection

@section('content')

    <div id="target"></div>

    <script id="template" type="text/ractive">
        @include('instructor.course.studentDetailsPage')
    </script>


    <script>
        var data = {!! $data !!};
        console.log(data);

        var ractive = new Ractive({
            target: '#target',
            template: '#template',
            data: data,
            delimiters: [ '[[', ']]' ],
        });
    </script>

@endsection
