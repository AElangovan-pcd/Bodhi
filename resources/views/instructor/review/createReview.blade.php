@extends('layouts.instructor')

@section('links')
    <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
    <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/review/landing')}}">Peer Review</a></li>
@stop

@section('JS')
    <script src="{{ asset('js/ractive.min.js') }}"></script>
    <script src="{{ asset('js/Sortable.min.js') }}"></script>
    <link href="{{ asset('css/summernote/summernote-bs4.css') }}" rel="stylesheet">
    <script src="{{ asset('js/summernote-bs4.js') }}"></script>
    <script src="{{ asset('js/exif.js') }}"></script>
    <script src="{{ asset('js/summernote-images.js') }}"></script>
@endsection

@section('content')

    <div id="target"></div>

    <script id="template" type="text/ractive">
        @include('instructor.review.createReviewPage')
    </script>

    <script type="text/javascript">
        const saveURL     = "{{url('/instructor/course/'.$course->id.'/review/saveAssignment')}}";

        var data = {!! $data !!};
        console.log(data);

        var ractive = new Ractive({
            el: "#target",
            template: "#template",
            data: data,
            delimiters: [ '[[', ']]' ],
        });

        ractive.set("saving", "Save Assignment");

        //Set the instruction editor to modify the viewable state.
        ractive.set("statusEdit",1);
        instructionEditor(1,data.instructions[1]);

        $('#description').summernote({
            placeholder: 'Assignment description that students will see.',
            height: 100,
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
                    summernoteOnImageUpload(files,"#description");
                },
                onChange: function(contents, $editable) {
                    ractive.set("description", contents)
                }
            }
        });
        $('#info').summernote({
            placeholder: 'Instructor notes not visible to students.',
            height: 100,
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
                    summernoteOnImageUpload(files,"#info");
                },
                onChange: function(contents, $editable) {
                    ractive.set("info", contents)
                }
            }
        });
        for(i = 0; i < data.questions.length; i++) {
            questionEditor(i,data.questions[i].description);
            var qnum = i;
            if(data.questions[i].type == 2)
                continue;
            Sortable.create(document.getElementById('choices_'+qnum), {
                animation: 150,
                handle: ".my-handle",
                onMove:function (evt) {
                    if(evt.from !== evt.to)  //Prevent moving between lists.
                        return false;
                },
                onEnd: function (evt) {
                    var order = this.toArray();
                    var items = [];
                    var choices = ractive.get("questions["+qnum+"].choices");
                    order.forEach(function(id) {
                        items.push(choices[id]);
                    });
                    console.log(items);
                    ractive.set("questions["+qnum+"].sortedChoices", items);
                }
            });
        }

        function instructionEditor(id, text) {
            var div = '#instructions_' + (id);
            console.log("init " + div);
            var status = ractive.get("status");
            $(div).summernote({
                placeholder: 'Instructions students see when the assignment is in the state: '+status[id],
                height: 100,
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
                        summernoteOnImageUpload(files,div);
                    },
                    onChange: function(contents, $editable) {
                        ractive.set("instructions["+id+"]", contents)
                    }
                }
            });
            $(div).summernote('code',text)
        }

        function questionEditor(id, text) {
            var div = '#description_' + (id);
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
                    ["picture"],
                    ["link"],
                    ["codeview"],
                    ["fullscreen"],
                    ["help"]
                ],
                callbacks: {
                    onImageUpload: function(files) {
                        summernoteOnImageUpload(files,div);
                    },
                    onChange: function(contents, $editable) {
                        ractive.set("questions["+id+"].description", contents)
                    }
                }
            });
            $(div).summernote('code',text)
        }

        ractive.on("addQuestion", function(event) {
            var questions = this.get("questions");
            var question = {
                type: event.node.id,
                description: "",
                choices: [],
                sortedChoices: [],
                required: true,
                id: -1,
                name: "",
            };
            questions.push(question);
            var qnum = questions.length-1;
            ractive.update();
            questionEditor(qnum);
            if(event.node.id == 2)
                return;  //Don't create the sortable object if it's a text question
            Sortable.create(document.getElementById('choices_'+qnum), {
                animation: 150,
                handle: ".my-handle",
                onMove:function (evt) {
                    if(evt.from !== evt.to)  //Prevent moving between lists.
                        return false;
                },
                onEnd: function (evt) {
                    var order = this.toArray();
                    var items = [];
                    var choices = ractive.get("questions["+qnum+"].choices");
                    order.forEach(function(id) {
                        items.push(choices[id]);
                    });
                    console.log(items);
                    ractive.set("questions["+qnum+"].sortedChoices", items);
                }
            });
        });

        ractive.on("removeQuestion", function(event) {
            ractive.splice("questions",event.get('@index'),1);
        });

        ractive.on("moveQuestion", function(event) {
           var questions = ractive.get("questions");
           var question = event.get();
           var index = questions.indexOf(question);

            if (event.node.id == "Down")
            {
                if (index + 1 < questions.length)
                {
                    console.log("Swapping down");
                    questions[index] = questions[index + 1];
                    questions[index + 1] = question;
                    ractive.set('questions', questions);
                    ractive.update();

                    //Destroy the description editors and reinstantiate them.
                    $('#description_' + (index)).summernote('destroy');
                    $('#description_' + (index+1)).summernote('destroy');
                    questionEditor(index, questions[index].description);
                    questionEditor(index+1, questions[index+1].description);
                }
            }
            else
            {
                if (index - 1 >= 0)
                {
                    console.log("Swapping up");
                    questions[index] = questions[index - 1];
                    questions[index - 1] = question;
                    ractive.set('questions', questions);
                    ractive.update();

                    //Destroy the description editors and reinstantiate them.
                    $('#description_' + (index)).summernote('destroy');
                    $('#description_' + (index-1)).summernote('destroy');
                    questionEditor(index, questions[index].description);
                    questionEditor(index-1, questions[index-1].description);
                }
            }

        });

        ractive.on("collapseQuestion", function(event) {
            var questions = ractive.get("questions");
            var question = event.get();
            var index = questions.indexOf(question);
            questions[index].collapsed = true;
            ractive.update();
        });

        ractive.on("expandQuestion", function(event) {
            var questions = ractive.get("questions");
            var question = event.get();
            var index = questions.indexOf(question);
            questions[index].collapsed = false;
            ractive.update();
        });

        ractive.on("collapseAll", function(event) {
            var questions = ractive.get("questions");
            for(i=0; i<questions.length; i++)
                questions[i].collapsed=true;
            ractive.update();
        });

        ractive.on("expandAll", function(event) {
            var questions = ractive.get("questions");
            for(i=0; i<questions.length; i++)
                questions[i].collapsed=false;
            ractive.update();
        });

        ractive.on("addChoice", function(event) {
            var question = event.get();
            var questions = this.get("questions");
            var index = questions.indexOf(question);
            var choice =  {
                name: "",
                value: "",
            };
            questions[index].choices.push(choice);
            questions[index].sortedChoices.push(choice);
            ractive.update();
        });

        ractive.on("removeChoice", function(event) {
            var qchoices = event.getParent().resolve();
            console.log(event.node.id);
            ractive.splice(qchoices,event.node.id,1);
        });

        ractive.on("check", function(event) {
            ractive.toggle(event.resolve()+".required");
        });

        ractive.on("response", function(event) {
            ractive.toggle("options.response");
        });

        ractive.on("responseView", function(event) {
            ractive.toggle("options.responseView");
        });

        ractive.on("types", function(event) {
            ractive.toggle("options.types");
        });

        ractive.on("addType", function(event) {
            var typesList = ractive.get("options.typesList");
            var type = {name: "",};
            typesList.push(type);
            ractive.update();
        });

        ractive.on("typesStyle", function(event) {
            ractive.set("options.typesReviewStyle",event.node.id);
        });

        ractive.on("changeStatus", function(event) {
            var index = event.node.id;
            ractive.set("statusEdit", index);
            $('#instructions_' + (index)).summernote('destroy');
            instructionEditor(index,data.instructions[index]);
            ractive.update();
        });

        ractive.on("save", function(event) {
            ractive.set("saving", "Saving...");
            console.log(data);
            $.post(saveURL,
                {
                    _token: "{{ csrf_token() }}",
                    data: JSON.stringify(data),
                })
                .done(function (response) {
                    response = JSON.parse(response);
                    console.log(response);
                    if(response.status==="success")
                        ractive.set("saving","Saved!");
                    data.id = response.id;
                    for(i=0; i<data.questions.length; i++) {
                        data.questions[i].id = response.qids[i];
                    }
                })
                .fail(function(xhr,status,error) {  //Deal with post failure.  Suggest page reload in case it's a session timeout.
                    ractive.set("saving","Error Saving");
                    console.log(error);
                });
        });

    </script>
@stop
