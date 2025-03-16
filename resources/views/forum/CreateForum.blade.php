@extends($instructor ? 'layouts.instructor' : 'layouts.student')

@if($instructor == 1)
@section('links')
    <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
    <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/forum/landing')}}">Forum</a></li>
@stop

@else
@section('links')
    <li class="nav-item"><a class="nav-link" href="{{url('course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
    <li class="nav-item"><a class="nav-link" href="{{url('course/'.$course->id.'/forum/landing')}}">Forum</a></li>
@stop
@endif

@section('JS')
    <script type="text/javascript" src="{{asset('js/ractive.min.js')}}"></script>
    <link href="{{ asset('css/summernote/summernote-bs4.css') }}" rel="stylesheet">
    <script src="{{ asset('js/summernote-bs4.js') }}"></script>
    <script src="{{ asset('js/exif.js') }}"></script>
    <script src="{{ asset('js/summernote-images.js') }}"></script>
@stop

@section('css')
    <link href="{{asset('/css/bootstrap-toggle.min.css')}}" rel="stylesheet">

@stop

@section('content')
    <div id="forum">
        <script type="text/ractive" id="template">
            @include('forum.createForumPage')
        </script>
    </div>

    <script type="text/javascript">
        var data = {!! $data !!};
        console.log(data);
        const saveURL = "{{url('course/'.$course->id.'/forum/save_forum')}}";
        data.saving = "Save Topic";

        var ractive = new Ractive({
            el: "#forum",
            template: "#template",
            data: data,
            delimiters: [ '[[', ']]' ]
        });

        $('#editor').summernote({
            placeholder: 'Enter your question here.',
            height: 200,
            toolbar: [
                ["style"],
                ["style", ["bold", "italic", "underline", "clear"]],
                ["font", ["strikethrough", "superscript", "subscript"]],
                ["fontsize", ["fontsize"]],
                ["color", ["color"]],
                ["para", ["ul", "ol", "paragraph"]],
                ["picture"],
                ["link"],
                ["codeview"],
                ["fullscreen"],
                ["help"]
            ],
            callbacks: {
                onImageUpload: function(files) {
                    summernoteOnImageUpload(files,"#editor");
                },
            }
        });

        ractive.on("insertEqn", function(event) {
            $('#editor').summernote("insertText", event.node.id);
        });

        ractive.on("check", function(event) {
            ractive.toggle("anonymous");
        });

        ractive.on("save", function (event) {
            console.log(event);
            var question = $('#editor').summernote('code');
            if (event.original.charCode)
                if (event.original.charCode != 13 && event.original.charCode != 32) { // return, space
                    console.log("nope");
                    return;
                }

            if (document.getElementById("title").value.length == 0) {
                ractive.set("saving", "Please add a title")
                return false;
            }

            if (question == "<p><br></p>") {
                ractive.set("saving", "Please add a question")
                return false;
            }

            console.log("saving");
            ractive.set("saving", "Saving!")

            var anonymous=0;
            var anon = ractive.get("anonymous");
            if (anon == true)
                anonymous=1;

            var data = {
                _token: "{{ csrf_token() }}",
                question: question,//ractive.get("forum_question"),
                title: document.getElementById("title").value,//ractive.get("forum_title"),
                course: ractive.get("course"),
                forum_id: ractive.get("forum_id"),
                forum_type: ractive.get("forum_type"),
                anonymous: anonymous,
                tags: document.getElementById("tags").value,//ractive.get("tags"),
            }

            $.post(saveURL, data, function (resp) {
                console.log(resp);
                ractive.animate("saving", "Saved");
                $("#back_btn").focus();
                $(location).attr('href',"{{url($instructor ? 'instructor/course/'.$course->id.'/forum/landing' : 'course/'.$course->id.'/forum/landing')}}");
            });
        });


    </script>

@stop
