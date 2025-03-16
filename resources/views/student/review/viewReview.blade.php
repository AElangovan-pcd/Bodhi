@extends('layouts.student')

@section('hand_links')
    <a id="hand_raise_button" >Raise your hand!</a>
    <li><a id="hand_lower_button" >Lower your hand!</a></li>
    <li><a class="btn btn-primary disabled" style="color:#fff" id="output"></a></li>
@stop
@section('links')
    <li class="nav-item"><a class="nav-link" href="{{url('course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
@stop

@section('JS')
    <script type="text/javascript" src="{{asset('js/ractive.min.js')}}"></script>
@stop

@section('content')
    <div id="target"></div>

    <script type="text/ractive" id="template">
        @include('student.review.viewReviewPage')
    </script>


    <script type="text/javascript">
        var data = {!! $data !!};
        console.log(data);

        var ractive = new Ractive({
            el       : "#target",
            template : "#template",
            data     : data,
            delimiters: [ '[[', ']]' ]
        });

        ractive.on("set_type", function(event) {
            ractive.set("type",event.node.id);
        });

        //Include the name in the image upload dialog
        $('#assignment_import').on('change',function(){
            //get the file name
            var fileName = $(this).val();
            fileName = fileName.replace(/^.*\\/, "");
            //replace the "Choose a file" label
            $(this).next('.custom-file-label').html(fileName);
            console.log(fileName);
        });

        $('#revision_import').on('change',function(){
            //get the file name
            var fileName = $(this).val();
            fileName = fileName.replace(/^.*\\/, "");
            //replace the "Choose a file" label
            $(this).next('.custom-file-label').html(fileName);
            console.log(fileName);
        });

        $('#response_import').on('change',function(){
            //get the file name
            var fileName = $(this).val();
            fileName = fileName.replace(/^.*\\/, "");
            //replace the "Choose a file" label
            $(this).next('.custom-file-label').html(fileName);
            console.log(fileName);
        });
    </script>

@endsection
