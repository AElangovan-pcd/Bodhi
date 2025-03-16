@extends($instructor ? 'layouts.instructor' : 'layouts.student')

@if($instructor == 1)
    @section('links')
        <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
        <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/assignment/'.$assignment->id.'/results/main')}}">Results</a></li>
        <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/assignment/'.$assignment->id.'/edit')}}">Edit</a></li>
    @stop

@else
    @section('links')
        <li class="nav-item"><a class="nav-link" href="{{url('course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
    @stop
@endif

@section('JS')
    <script src="{{ asset('js/ractive.min.js') }}"></script>
    <script src="{{ asset('js/raphael.min.js') }}"></script>
    <script src="{{ asset('js/kekule.min.js') }}"></script>
    <link href="{{ asset('css/kekule/kekule.css') }}" type="text/css" rel="stylesheet">
    <link href="{{ asset('css/summernote/summernote-bs4.css') }}" rel="stylesheet">
    <script src="{{ asset('js/summernote-bs4.js') }}"></script>
    <script src="{{ asset('js/exif.js') }}"></script>
    <script src="{{ asset('js/summernote-images.js') }}"></script>
    <script src="{{ asset('js/summernote-image-attributes.js') }}"></script>
    <script src='{{ asset('/js/moment.min.js') }}'></script>
    <style>
        #composerSrc, #composerTarget, #codeViewer
        {
            height: 400px;
            float: left;
        }
        #composerSrc, #composerTarget
        {
            width: 550px;
        }
        #codeViewer
        {
            font-family: "Courier New", Courier, monospace;
            white-space: pre;
            width: 400px;
        }
        .Positive { color: green; }
        .Negative { color: red; }
    </style>

@endsection

@section('content')

    <div id="qtarget"></div>

    <script id="qtemplate" type="text/ractive">
        @include('assignments.AssignmentViewPage')
    </script>

    @include('assignments.assignment_js')

@stop

