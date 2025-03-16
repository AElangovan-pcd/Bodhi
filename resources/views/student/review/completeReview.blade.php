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
    <script src="{{ asset('js/exif.js') }}"></script>
    <script src="{{ asset('js/summernote-images.js') }}"></script>
@stop

@section('content')
    <div id="target"></div>

    <script type="text/ractive" id="template">
        @include('student.review.completeReviewPage')
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

        for(i = 0; i < data.review.questions.length; i++) {
            if(data.review.questions[i].answers[0] == undefined)
                data.review.questions[i].answers.push({selected:"",text:""});
            if(data.review.questions[i].type==2)
                questionEditor(i,data.review.questions[i].text);
        }

        function questionEditor(id, text) {
            var div = '#text_' + (id);
            console.log("init " + div);
            $(div).summernote({
                placeholder: 'Question description.',
                height: 100,
                toolbar: [
                    ["style"],
                    ["style", ["bold", "italic", "underline", "clear"]],
                    ["font", ["strikethrough", "superscript", "subscript"]],
                    ["fontsize", ["fontsize"]],
                    ["color", ["color"]],
                    ["para", ["ul", "ol", "paragraph"]],
                    ["codeview"],
                    ["fullscreen"],
                    ["help"]
                ],
                callbacks: {
                    onImageUpload: function(files) {
                        summernoteOnImageUpload(files,div);
                    },
                    onChange: function(contents, $editable) {
                        ractive.set("review.questions["+id+"].answers[0].text", contents)
                    }
                }
            });
            $(div).summernote('code',text)
        }

        ractive.on("select", function(event) {
            var qnum = event.node.getAttribute('q-data');
            var questions = ractive.get('review.questions');
            var choice = event.node.id;
            for(i=0;i<questions[qnum].choices.length; i++)
                questions[qnum].answers[0].selected = choice;
            ractive.set("questions",questions);
        });

        ractive.on("save", function(event) {
            var questions = ractive.get('review.questions');
            ractive.set("save_msg","Saving...");
            ractive.set("save_error",null);
            for(i=0; i<questions.length; i++) {
                if(questions[i].type == 0 || questions[i].type == 1) {
                    if(questions[i].answers[0].selected !== "")
                        questions[i].incomplete = false;
                    else
                        questions[i].incomplete = true;
                }
                if(questions[i].type == 2) {
                    questions[i].incomplete = false;
                    if (questions[i].answers[0].text === undefined || questions[i].answers[0].text === null || questions[i].answers[0].text === "" || questions[i].answers[0].text === "<p><br></p>") {
                        questions[i].incomplete = true;
                    }
                }

                if(questions[i].required && questions[i].incomplete) {
                    console.log("Question " + i + " is required.");

                    var error=true;
                }
            }
            if(error) {
                ractive.set("save_error","Please answer the required questions above before saving.");
                ractive.set("save_msg","Save Review");
                ractive.update();
                return;
            }
            ractive.update();
            $.post(data.job_id+'/save', {_token: "{{ csrf_token() }}", questions: questions},
                function (resp)
                {
                    console.log(resp);
                    if(resp==="Closed") {
                        ractive.set("save_error","Reviews are not currently being accepted.")
                        ractive.set("save_msg","Save");
                    }
                    ractive.set("save_msg","Saved");
                });
        });

    </script>

@endsection
