@extends('layouts.student')

@section('hand_links')
    <a id="hand_raise_button" >Raise your hand!</a>
    <li><a id="hand_lower_button" >Lower your hand!</a></li>
    <li><a class="btn btn-primary disabled" style="color:#fff" id="output"></a></li>
@stop
@section('links')
    <li class="nav-item"><a class="nav-link" href="{{url('course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
    <li class="nav-item"><a class="nav-link" href="{{url('course/'.$course->id.'/review/'.$review->id.'/view')}}">{{$review->name}}</a></li>
@stop

@section('JS')
    <script type="text/javascript" src="{{asset('js/ractive.min.js')}}"></script>
    <link href="{{ asset('css/summernote/summernote-bs4.css') }}" rel="stylesheet">
    <script src="{{ asset('js/summernote-bs4.js') }}"></script>
@stop

@section('content')
    <div id="target"></div>

    <script type="text/ractive" id="template">
        @include('student.review.viewResultsPage')
    </script>


    <script type="text/javascript">
        var data = {!! $data !!};
        data.save_msg = "Save Review";
        console.log(data);

        var ractive = new Ractive({
            el       : "#target",
            template : "#template",
            data     : data,
            delimiters: [ '[[', ']]' ]
        });

        ractive.on("gatherComments", function(event) {
            var jobs = ractive.get("jobs");
            var questions = ractive.get("questions");
            var comments = "";
            for(i=0; i<jobs.length;i++) {
                comments += '<h3>Reviewer '+(i+1)+'\n\n</h3>';
                for(j=0; j<jobs[i].answers.length; j++) {
                    if(questions[j].type==2)
                        comments += jobs[i].answers[j].text + '\n';
                }
                comments += '\n\n';
            }
            ractive.set("comments",comments);
            commentsEditor()
        });

        function commentsEditor(text) {
            var div = '#modal_text';
            console.log("init " + div);
            $(div).summernote({
                placeholder: 'Written comments.',
                height: 200,
                toolbar: [
                    ["fullscreen"],
                ],
            });
            $(div).summernote('code',text);
        }

    </script>

@endsection
