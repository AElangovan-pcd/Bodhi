@extends('layouts.instructor')

@section('links')
    <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
@stop

@section('JS')
    <script src="{{ asset('js/ractive.min.js') }}"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src='{{ asset('/js/moment.min.js') }}'></script>
@endsection

@section('content')

    <div id="target"></div>

    <script id="template" type="text/ractive">
        @include('instructor.review.landingPage')
    </script>

    <script type="text/javascript">

        var data = {!! $data !!};
        data.range = function ( low, high ) {
            var range = [];
            for ( i = low; i <= high; i += 1 ) {
                range.push( i );
            }
            return range;
        }
        console.log(data);

        var ractive = new Ractive({
            el: "#target",
            template: "#template",
            data: data,
            delimiters: [ '[[', ']]' ],
        });

        //Include the name in the upload dialog
        $('#assignment_import').on('change',function(){
            //get the file name
            var fileName = $(this).val();
            fileName = fileName.replace(/^.*\\/, "");
            //replace the "Choose a file" label
            $(this).next('.custom-file-label').html(fileName);
            console.log(fileName);
        });

    </script>
@stop
