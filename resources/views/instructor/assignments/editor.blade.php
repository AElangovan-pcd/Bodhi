@extends('layouts.instructor')

@section('JS')
    <script src="{{ asset('js/ractive.min.js') }}"></script>

    <script src="{{ asset('js/jquery.custom-animations.js') }}"></script>

    <!-- include codemirror (codemirror.css, codemirror.js, xml.js, formatting.js) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css" integrity="sha512-uf06llspW44/LZpHzHT6qBOIVODjWtv4MxCricRxkzvopAlSWnTf6hpZTFxuuZcuNE9CBQhqE0Seu1CoRk84nQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/monokai.min.css" integrity="sha512-R6PH4vSzF2Yxjdvb2p2FA06yWul+U0PDDav4b/od/oXf9Iw37zl10plvwOXelrjV2Ai7Eo3vyHeyFUjhXdBCVQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js" integrity="sha512-xwrAU5yhWwdTvvmMNheFn9IyuDbl/Kyghz2J3wQRDR8tyNmT8ZIYOd0V3iPYY/g4XdNPy0n/g0NvqGu9f0fPJQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js" integrity="sha512-UWfBe6aiZInvbBlm91IURVHHTwigTPtM3M4B73a8AykmxhDWq4EC/V2rgUNiLgmd/i0y0KWHolqmVQyJ35JsNA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/codemirror/2.36.0/formatting.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/search/searchcursor.min.js" integrity="sha512-nICucPRt5hIHxMWob9BGrtclKdO9j4lzz3aY/lRNJzUoxT+QODCEYxiB5sywrKdAMYXjshdM9UpRCwlHKDb9Yw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/search/search.min.js" integrity="sha512-jbqegru8OL6hPtAAWTI/+r30j95Qdyx9AESgBC1dB6Ldjqzc292pDWFdLPVWHpgyYM5V//zdNjP8PjdVlRPmXQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <link href="{{ asset('css/summernote/summernote-bs4.css') }}" rel="stylesheet">
    <script src="{{ asset('js/summernote-bs4.js') }}"></script>
    <script src="{{ asset('js/exif.js') }}"></script>
    <script src="{{ asset('js/summernote-images.js') }}"></script>
    <script src="{{ asset('js/summernote-image-attributes.js') }}"></script>
    <script src="{{ asset('js/summernote-table-headers.js') }}"></script>
    <script src="{{ asset('js/summernote-a11y.js') }}"></script>
    <script src="{{ asset('js/raphael.min.js') }}"></script>
    <script src="{{ asset('js/kekule.min.js') }}"></script>
    <link href="{{ asset('css/kekule/kekule.css') }}" type="text/css" rel="stylesheet">
    <script src="{{ asset('js/Sortable.min.js') }}"></script>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.9/flatpickr.min.css" rel="stylesheet" media="all">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.9/flatpickr.min.js"></script>

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

@section('links')
    @if($instructor == 1)
        <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
        @if(isset($assignment->id))
            <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/assignment/'.$assignment->id.'/results/main')}}">Results</a></li>
            <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/assignment/'.$assignment->id.'/view')}}">View</a></li>
        @endif
    @else
        <li class="nav-item"><a class="nav-link" href="{{url('course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
        @if(isset($assignment->id))
            <li class="nav-item"><a class="nav-link" href="{{url('course/'.$course->id.'/LA/assignment/'.$assignment->id.'/results/main')}}">Results</a></li>
            <li class="nav-item"><a class="nav-link" href="{{url('course/'.$course->id.'/assignment/'.$assignment->id.'/view')}}">View</a></li>
        @endif
    @endif
@endsection

@section('content')

    <div id="target"></div>

    <script id="template" type="text/ractive">
        @include('instructor.assignments.editorPage')
    </script>


    <script>
        var data = {!! $data !!};
        console.log(data);

        if(data.assignment.options == null || (Array.isArray(data.assignment.options) && data.assignment.options.length === 0))
            data.assignment.options = {};  //Workaround for converting empty array to object

        if(Array.isArray(data.assignment.options))  //If options is an array, make it an object.
            data.assignment.options = data.assignment.options.reduce(function(acc,cur,i) {
                acc[i] = cur;
                return acc;
            }, {});
        if(data.assignment.options.quiz == undefined || Array.isArray(data.assignment.options.quiz) && data.assignment.options.quiz.length == 0) {
            data.assignment.options.quiz = {pages: []};  //Initialize the options with something so that the options will persist as an object rather than an array.
        }

        data.questions.forEach(q => {
            //Initialize empty options arrays as objects to avoid saving problems.
            if(Array.isArray(q.options) && q.options.length === 0)
                q.options = {gradedFlag : false};
        });

        data.chargeDefault = function(value) {
            return (value == null ? 'Charge' : value);
        };

        data.phaseDefault = function(value) {
            return (value == null ? 'Phase' : value);
        }

        // Constants
        data.STANDARD_QUESTION = 1;
        data.SHORT_ANSWER = 2;
        data.SIMPLE_QUESTION = 3;
        data.UNANSWERED_QUESTION = 4;
        data.SIMPLE_TEXT_QUESTION = 5;
        data.MOLECULE_QUESTION = 6;
        data.MULTIPLE_CHOICE_QUESTION = 7;
        data.REACTION_QUESTION = 8;
        data.types=["","Standard Question","Graded Written Question","Simple Question","Information Block","Simple Text Question","Molecule Question","Multiple Choice Question","Chemical Reaction"];

        let editorSettings = {
            height: 100,
            codemirror: { // codemirror options
                theme: 'monokai',
                lineWrapping: true,
                lineNumbers: true,
            },
            tableClassName: function() {
                $(this)
                    .addClass("table table-bordered w-auto")
            },
            toolbar: [
                ['a11y',['a11y']],
                ["style"],
                ["style", ["bold", "italic", "underline", "clear"]],
                ["font", ["strikethrough", "superscript", "subscript"]],
                ["fontsize", ["fontsize"]],
                ["color", ["color"]],
                ["para", ["ul", "ol", "paragraph"]],
                ["picture"],
                ["link"],
                ["table"],
                ["codeview"],
                ["fullscreen"],
                ["help"]
            ],
            a11y:{
                icon: '<i id="summernote-a11y" class="note-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 14 14" width="12" height="12"><path d="m 6.9999997,1 c -0.620817,0 -1.125,0.50296 -1.125,1.125 0,0.62204 0.502959,1.126 1.125,1.125 0.620816,0 1.1250001,-0.50296 1.1250001,-1.125 C 8.1249998,1.50418 7.6220407,1 6.9999997,1 Z m -5.0878906,1.38867 -0.2792969,0.69727 3.8652344,1.66797 0,3.00195 L 3.9589841,12.73438 4.6621091,13 6.8398435,8.13086 l 0.3222656,0 L 9.3378903,13 10.041016,12.73438 8.5019528,7.75391 l 0,-3 3.8652352,-1.66797 -0.279297,-0.69727 -4.7128913,1.61328 -0.75,0 -4.7128906,-1.61328 z"/></svg></i>',
                langFile: "{{ asset('css/summernote/a11y-en-US.css') }}",
            },
            popover: {
                image: [
                    ['custom', ['imageAttributes']],
                    ['imagesize', ['resizeFull', 'resizeHalf', 'resizeQuarter', 'resizeNone']],
                    ['float', ['floatLeft', 'floatRight', 'floatNone']],
                    ['remove', ['removeMedia']]
                ],
                table: [
                    ['add', ['addRowDown', 'addRowUp', 'addColLeft', 'addColRight']],
                    ['delete', ['deleteRow', 'deleteCol', 'deleteTable']],
                    ['custom', ['tableHeaders']],
                ],
            },
            lang: 'en-US', // Change to your chosen language
            imageAttributes:{
                icon:'<i class="note-icon-pencil"/>',
                removeEmpty:false, // true = remove attributes | false = leave empty if present
                disableUpload: false // true = don't display Upload Options | Display Upload Options
            },
            callbacks: {
                onImageUpload: function(files) {
                    summernoteOnImageUpload(files,"#description");
                },
                onChange: function(contents, $editable) {
                    ractive.set("assignment.description", contents)
                }
            }
        };

        function startSummernote(div, settings, placeholder, onChange) {
            settings.placeholder = placeholder;
            settings.callbacks = {
                onImageUpload: function(files) {
                    summernoteOnImageUpload(files,div);
                },
                onChange: onChange,
            };
            $(div).summernote(settings);
            $(div).on('summernote.codeview.toggled', (event) => {
                //Coolapse the base64 image tags in the codeMirror instance
                let codeMirror = $(div).next().find('.note-codable').data('cmEditor');
                let searchq = /base64[^\"]+/
                let cursor = codeMirror.getSearchCursor(searchq);
                while(cursor.findNext()) {
                    codeMirror.markText(cursor.from(),cursor.to(), {collapsed: true});
                }
            });
        }

        function getQuestionOnChange(q_index) {
            return function(contents, $editable) {
                console.log("onchange "+q_index);
                ractive.set("questions."+q_index+".description",contents);
            };
        }

        $(document).ready(function() {

            //Initialize calendar
            let pickr = flatpickr("#closes_at",
                {
                    enableTime: true,
                    defaultDate: ractive.get("assignment.closes_at"),
                    dateFormat: "Y-m-d H:i",
                    altInput: true,
                    altInputClass: "inline-block",
                    altFormat: "F j, Y H:i",
                    onChange: function(selectedDates, dateStr, instance) {
                        console.log(" closes_at change date to " + dateStr);
                        ractive.set("assignment.closes_at", dateStr);
                    }
                });

            //Initialize editors
            startSummernote('#description',
                editorSettings,
                'Assignment description that students will see.',
                function(contents, $editable) {
                    ractive.set("assignment.description", contents)
                },
            );
            startSummernote('#info',
                editorSettings,
                'Instructor notes not visible to students.',
                function(contents, $editable) {
                    ractive.set("assignment.info", contents)
                },
            );

            for(i = 0; i < data.questions.length; i++) {
                questionEditor(i,data.questions[i].description);
                if(data.questions[i].extra == null)
                    data.questions[i].extra = {
                        available : false,
                        text : "",
                    };
                extraEditor(i,data.questions[i].extra.text);
                if(data.questions[i].type===data.MOLECULE_QUESTION) {
                    attachKekule(i, data.questions[i].molecule.drawing);
                }
                else if(data.questions[i].type===data.MULTIPLE_CHOICE_QUESTION)
                    initMCSortable(i);
            }
        });

            @if($instructor == 1)
        var POST_PATH = "{{url('instructor/course/'.$course->id.'/assignment/saveEdit')}}";
            @else
        var POST_PATH = "{{url('course/'.$course->id.'/LA/assignment/saveEdit')}}";
        @endif

            data.hasComputed = function(index) {
            question = this.get("questions")[index];
            return question.variables.filter(variable => (variable.type == 3)).length > 0;
        }

        //For molecule question type
        data.kekules = [];

        var ractive = new Ractive({
            target: '#target',
            template: '#template',
            data : data,
            delimiters: [ '[[', ']]' ],
            computed: {
                questionNumbers: function () {
                    let questions = this.get('questions');
                    let computed = [];
                    let cnt = 0;
                    questions.forEach(q => {
                            if(q.options == null || q.options.excludeFromNumbering !== true)
                                cnt++;

                            computed.push(cnt)

                        }
                    );
                    return computed;
                }
            },
        });

        data.questions.forEach((q, qind) => {
            //Initialize empty options arrays as objects to avoid saving problems.
            if(Array.isArray(q.options) && q.options.length === 0)
                q.options = {gradedFlag : false};
            //Load reaction previews
            if(parseInt(q.type) === 8)
                preview_reaction(qind);
        });

        function getQuestion(keypath)
        {
            var pattern = /questions.(\d+)/;
            var id = pattern.exec(keypath)[1];
            return ractive.get("questions." + id);
        }

        function setQuestion(keypath, question)
        {
            var pattern = /questions.(\d+)/;
            var id = pattern.exec(keypath)[1];
            ractive.set("questions." + id, question);
        }

        // Add actions
        function addQuestion(qType)
        {
            var questions = ractive.get("questions");
            var newHTMLid = "q_" + questions.length + "_focus";
            var defCond = {
                equation: "1",
                id: "-1",
                name: "",
                points: "0",
                question_id: "-1",
                result: "",
                type: "1"
            };

            var newQ =
                {
                    active: "0",
                    deferred: 0,
                    answer: "",
                    assignment_id: data.assignment.id,
                    conditions: [ defCond ],
                    description: "",
                    feedback: "Incorrect answer",
                    id: "-1",
                    inter_variables: [],
                    max_points: "1",
                    name: "",
                    order: "1",
                    tolerance: "",
                    tolerance_type: "0",
                    type: qType,
                    variables: [],
                    responses: [],
                    html_id: newHTMLid,
                    extra: {
                        available: false,
                        text: "",
                    },
                    options: {},
                    choices: [],
                };

            if(qType === data.MOLECULE_QUESTION)
                newQ.molecule = {
                    drawing: '',
                    explicitH: false,
                    lonePairs: false,
                    halogens: false,
                    evalType: 'structure',  //Choices: structure, formula
                    matchType: 'single', //Choices: single, any, some, all
                    structureNum: 1, //Number of matches required for matchType='some' or for evalType='formula'.
                    editor: 'simple',  //Choices: simple, full
                    groups: [],
                    groupMatchType: 'ignore', //Choices: ignore, any, all, each, each/all
                };

            if(qType === data.MULTIPLE_CHOICE_QUESTION) {
                newQ.choices = [
                    {
                        id: 0,
                        description: '',
                    },
                ];
                newQ.options = {
                    MC: {
                        type: 'single',
                        shuffleType: 'none',
                    }
                };
            }
            if(qType === data.REACTION_QUESTION) {
                newQ.answer = {
                    scoringMode: 'simple',
                    feedbackMode: 'simple',
                    balanceMode: 'any',
                    feedback: {
                        correct: 'Correct!',
                        incorrect: 'Incorrect.',
                    },
                };
                newQ.options = {
                    reaction: {
                        phase: true,
                    }
                }
            }

            questions.push(newQ);
            ractive.update();

            var id = questions.length -1;
            questionEditor(id);
            extraEditor(id);

            if(qType === data.MULTIPLE_CHOICE_QUESTION)
                initMCSortable(id);

            console.log(newHTMLid);
            $("#" + newHTMLid).focus();
            $('html, body').animate({scrollTop: $("#" + newHTMLid).offset().top}, 'slow');

            return id;
        }

        function questionEditor(id, text) {
            let div = "#description_" + id;
            startSummernote(div,
                editorSettings,
                'Question description',
                getQuestionOnChange(id),
            );

            $(div).summernote('code',text);
        }

        function extraEditor(id, text) {
            var div = '#extra_' + (id);
            $(div).summernote({
                placeholder: 'Question extra info.',
                height: 100,
                codemirror: { // codemirror options
                    theme: 'monokai',
                    lineWrapping: true,
                    lineNumbers: true,
                },
                toolbar: [
                    ['a11y',['a11y']],
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
                a11y:{
                    icon: '<i id="summernote-a11y" class="note-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 14 14" width="12" height="12"><path d="m 6.9999997,1 c -0.620817,0 -1.125,0.50296 -1.125,1.125 0,0.62204 0.502959,1.126 1.125,1.125 0.620816,0 1.1250001,-0.50296 1.1250001,-1.125 C 8.1249998,1.50418 7.6220407,1 6.9999997,1 Z m -5.0878906,1.38867 -0.2792969,0.69727 3.8652344,1.66797 0,3.00195 L 3.9589841,12.73438 4.6621091,13 6.8398435,8.13086 l 0.3222656,0 L 9.3378903,13 10.041016,12.73438 8.5019528,7.75391 l 0,-3 3.8652352,-1.66797 -0.279297,-0.69727 -4.7128913,1.61328 -0.75,0 -4.7128906,-1.61328 z"/></svg></i>',
                    langFile: "{{ asset('css/summernote/a11y-en-US.css') }}",
                },
                callbacks: {
                    onImageUpload: function(files) {
                        summernoteOnImageUpload(files,div);
                    },
                    onChange: function(contents, $editable) {
                        ractive.set("questions["+id+"].extra.text", contents);
                    }
                }
            });
            $(div).summernote('code',text);
            $(div).on('summernote.codeview.toggled', (event) => {
                //Coolapse the base64 image tags in the codeMirror instance
                let codeMirror = $(div).next().find('.note-codable').data('cmEditor');
                let searchq = /base64[^\"]+/
                let cursor = codeMirror.getSearchCursor(searchq);
                while(cursor.findNext()) {
                    codeMirror.markText(cursor.from(),cursor.to(), {collapsed: true});
                }
            });
        }

        ractive.on("choiceEditor", function(context, q, c) {
            ractive.set('questions.'+q+'.editingChoice',true);
            let div = '#question_'+q+'_choiceEditor';
            $(div).summernote('destroy');
            $(div).summernote({
                placeholder: 'Choice text',
                height: 100,
                toolbar: [
                    ['a11y',['a11y']],
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
                a11y:{
                    icon: '<i id="summernote-a11y" class="note-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 14 14" width="12" height="12"><path d="m 6.9999997,1 c -0.620817,0 -1.125,0.50296 -1.125,1.125 0,0.62204 0.502959,1.126 1.125,1.125 0.620816,0 1.1250001,-0.50296 1.1250001,-1.125 C 8.1249998,1.50418 7.6220407,1 6.9999997,1 Z m -5.0878906,1.38867 -0.2792969,0.69727 3.8652344,1.66797 0,3.00195 L 3.9589841,12.73438 4.6621091,13 6.8398435,8.13086 l 0.3222656,0 L 9.3378903,13 10.041016,12.73438 8.5019528,7.75391 l 0,-3 3.8652352,-1.66797 -0.279297,-0.69727 -4.7128913,1.61328 -0.75,0 -4.7128906,-1.61328 z"/></svg></i>',
                    langFile: "{{ asset('css/summernote/a11y-en-US.css') }}",
                },
                callbacks: {
                    onImageUpload: function(files) {
                        summernoteOnImageUpload(files,div);
                    },
                    onChange: function(contents, $editable) {
                        ractive.set('questions.'+q+'.choices.'+c+'.description', contents);
                    }
                },
                focus: true,
            });
            $(div).summernote('code',ractive.get('questions.'+q+'.choices.'+c+'.description'));
        });

        ractive.on("closeChoiceEditor", function(context, q) {
            let div = '#question_'+q+'_choiceEditor';
            $(div).summernote('destroy');
            ractive.set('questions.'+q+'.editingChoice',false);
        });

        ractive.on("addQuestion", function(event)
        {
            addQuestion(data.STANDARD_QUESTION);
        });

        ractive.on("addSimple", function(event)
        {
            addQuestion(data.SIMPLE_QUESTION);
        });

        ractive.on("addSimpleText", function(event) {
            addQuestion(data.SIMPLE_TEXT_QUESTION);
        });

        ractive.on("addShort", function(event)
        {
            addQuestion(data.SHORT_ANSWER);
        });

        ractive.on("addUnanswered", function(event)
        {
            addQuestion(data.UNANSWERED_QUESTION);
        });

        ractive.on("addMolecule", function(event) {
            var index = addQuestion(data.MOLECULE_QUESTION);
            var composer = new Kekule.Editor.Composer(document.getElementById('molecule_'+index));
            /*composer.on('operChange', function (e) {  //This is how we will capture updates to the molecule canvas.
                //See the events section here: http://partridgejiang.github.io/Kekule.js/documents/api/chemWidget/Kekule.Editor.BaseEditor.html
               console.log("kekule change test",index);
            });*/
            var kekules = ractive.get("kekules");
            kekules[index]=composer;
            ractive.set("kekules",kekules);
        });

        ractive.on("addMultipleChoice", function(event)
        {
            addQuestion(data.MULTIPLE_CHOICE_QUESTION);
        });

        ractive.on("addReaction", function(context) {
            addQuestion(data.REACTION_QUESTION);
        });

        ractive.on("toggleH", function(event) {
            var question = event.get();
            question.molecule.explicitH = !question.molecule.explicitH;
            ractive.update();
        });

        ractive.on("toggleLP", function(event) {
            var question = event.get();
            question.molecule.lonePairs = !question.molecule.lonePairs;
            ractive.update();
        });

        ractive.on("toggleHalogens", function(event) {
            var question = event.get();
            question.molecule.halogens = !question.molecule.halogens;
            ractive.update();
        });

        ractive.on("toggleMoleculeEditor", function(event) {
            var question = event.get();
            question.molecule.editor = question.molecule.editor === 'simple' ? 'full' : 'simple';
            ractive.update();
        });

        ractive.on("toggleMoleculeEvalType", function(event) {
            var question = event.get();
            question.molecule.evalType = question.molecule.evalType === 'structure' ? 'formula' : 'structure';
            ractive.update();
        });

        ractive.on("toggleMoleculeMatchType", function(event) {
            var question = event.get();
            question.molecule.matchType = question.molecule.matchType === 'single' ? 'any' : question.molecule.matchType === 'any' ? 'all' : question.molecule.matchType === 'all' ? 'some' : 'single';
            ractive.update();
        });

        ractive.on("toggleGroupMatchType", function(event) {
            var question = event.get();
            question.molecule.groupMatchType = question.molecule.groupMatchType === 'ignore' ? 'any' : question.molecule.groupMatchType === 'any' ? 'all' : question.molecule.groupMatchType === 'all' ? 'each' : question.molecule.groupMatchType === 'each' ? 'each/all' : 'ignore';
            ractive.update();
        });

        ractive.on("toggleIncludedGroups", function(event) {
            var question = event.get();
            var group = event.node.id;
            if(question.molecule.groups.includes(group)) {
                var index = question.molecule.groups.indexOf(group);
                if (index !== -1) question.molecule.groups.splice(index, 1);
            }
            else
                question.molecule.groups.push(group);
            ractive.update();
        });

        ractive.on("testMolecule", function(event) {
            var kekules = ractive.get("kekules");
            composer = kekules[event.node.id];
            var mols = composer.exportObjs(Kekule.Molecule);
            var mol = composer.getChemObj();
            var data = Kekule.IO.saveFormatData(mol, 'Kekule-JSON');
            console.log(data);
            // dump information
            var msg = 'Molecule count: ' + mols.length + '\n';
            for (var i = 0, l = mols.length; i < l; ++i)
            {
                var mol = mols[i];
                console.log(mol.calcFormula().getText());
                //console.log(mol.calcFormula());
                msg += '--------------------\n' + Kekule.IO.saveFormatData(mol, 'Kekule-JSON') + '\n';
            }
            console.log(msg);
            // How to compare two molecules
            if(mols.length==2) {
                console.log(mols[0].isSameStructureWith(mols[1]));
                console.log(mols[0].calcFormula()===mols[1].calcFormula());
            }

        });


        ractive.on("extra", function(event) {
            var question=event.get();
            question.extra.available = (question.extra.available == null || question.extra.available === false);
            ractive.update();
        });


        // Question actions
        ractive.on("moveQuestion", function(event){
            let questions = ractive.get("questions");
            let question = event.get();
            let index = questions.indexOf(question);
            let index2;

            if (event.node.id === "Down")
                index2 = index+1;
            else if (event.node.id === "Up")
                index2 = index-1;
            if(index2 < 0 || index2 >=questions.length) {
                console.log("out of bounds");
                return;
            }
            //Swap the questions
            [questions[index], questions[index2]] = [questions[index2], questions[index]];
            ractive.update();

            //Reinstantiate the rich text editors
            questionEditor(index,data.questions[index].description);
            questionEditor(index2,data.questions[index2].description);

            //Move Kekule editors if present
            let molData;
            if(questions[index].type===data.MOLECULE_QUESTION) {
                document.getElementById('molecule_'+index).innerHTML='';
                molData = Kekule.IO.saveFormatData(data.kekules[index2].getChemObj(), 'Kekule-JSON');
            }
            if(questions[index2].type===data.MOLECULE_QUESTION) {
                document.getElementById('molecule_'+(index2)).innerHTML='';
                molData1 = Kekule.IO.saveFormatData(data.kekules[index].getChemObj(), 'Kekule-JSON');
            }
            if(questions[index].type===data.MOLECULE_QUESTION)
                attachKekule(index, molData);
            if(questions[index2].type===data.MOLECULE_QUESTION)
                attachKekule(index2,molData1);
        });

        ractive.on("duplicateQuestion", function(context, index) {
            let question = context.get();
            let questions = ractive.get("questions");

            let newQuestion = JSON.parse(JSON.stringify(question)); //Clone question
            if(newQuestion.type === data.MOLECULE_QUESTION)
                newQuestion.molecule.drawing = Kekule.IO.saveFormatData(data.kekules[index].getChemObj(), 'Kekule-JSON'); //Store the molecule

            addDuplicatedQuestion(newQuestion);
        });

        function addDuplicatedQuestion(question) {
            let questions = ractive.get("questions");
            question.id = -1; //Set as a new question
            question.parent_question_id = null; //If it had a parent in original location, unlink it.

            questions.push(question);
            ractive.set("questions",questions);
            ractive.update();

            let id = questions.length -1;
            questionEditor(id);
            extraEditor(id);
            if(questions[id].type==data.MOLECULE_QUESTION) {
                attachKekule(id, question.molecule.drawing);
            }

            let newHTMLid = "q_" + (questions.length-1) + "_focus";

            $("#" + newHTMLid).focus();
            $('html, body').animate({scrollTop: $("#" + newHTMLid).offset().top}, 'slow');
        }

        ractive.on("getJSON", function(context) {
            let question = context.get();
            ractive.set("question_JSON", JSON.stringify(question));

        });

        ractive.on("copyJSON", function(context) {
            let text = document.getElementById('question_JSON_textarea');
            text.select();
            document.execCommand("copy");
        });

        ractive.on("importJSON", function(context) {
            let imported_JSON = ractive.get("pasted_JSON");
            let question = JSON.parse(imported_JSON);
            addDuplicatedQuestion(question);
            $('#paste_JSON_modal').modal('hide');
            ractive.set("pasted_JSON", null);
        });

        function attachKekule(index,molData) {
            var kekules = ractive.get("kekules");
            var composer = new Kekule.Editor.Composer(document.getElementById('molecule_'+index));
            var mol = Kekule.IO.loadFormatData(molData, 'Kekule-JSON');
            composer.setChemObj(mol);
            kekules[index]=composer;
            ractive.set("kekules",kekules);
        }

        function getMolecules() {
            var questions = ractive.get("questions");
            var kekules = ractive.get("kekules");
            for(i=0; i<questions.length; i++) {
                if(questions[i].type==data.MOLECULE_QUESTION) {
                    composer = kekules[i];
                    var drawing = composer.getChemObj();
                    questions[i].molecule.drawing = Kekule.IO.saveFormatData(drawing, 'Kekule-JSON');
                }
            }
        }

        ractive.on("deleteQuestion", function(event){
            var html_panel = event.node.parentNode.parentNode;
            //$(html_panel).blindLeftToggle();  //This animation causes a problem if you delete anything other than the last question.
            var questions = ractive.get("questions");
            var question = event.get();
            var index = questions.indexOf(question);

            setTimeout(function(){
                questions.splice(index, 1);
                ractive.set('questions', questions);

                //Reinstantiate editors
                for(var i=index; i<questions.length; i++) {
                    questionEditor(i, questions[i].description);
                    extraEditor(i, questions[i].extra.text);

                    if(questions[i].type === data.MOLECULE_QUESTION) {
                        document.getElementById('molecule_'+i).innerHTML='';
                        var molData = Kekule.IO.saveFormatData(data.kekules[i+1].getChemObj(), 'Kekule-JSON');
                        attachKekule(i, molData);
                    }
                }
            }, 400);

        });

        ractive.on("collapseQuestion", function(event) {
            var questions = ractive.get("questions");
            var question = event.get();
            var index = questions.indexOf(question);
            questions[index].collapsed = true;
            ractive.set("questions", questions);
            ractive.update();
        });

        ractive.on("expandQuestion", function(event) {
            var questions = ractive.get("questions");
            var question = event.get();
            var index = questions.indexOf(question);
            questions[index].collapsed = false;
            ractive.set("questions", questions);
            ractive.update();
        });

        ractive.on("collapseAll", function(event) {
            var questions = ractive.get("questions");
            for(i=0; i<questions.length; i++)
                questions[i].collapsed=true;
            ractive.set("questions",questions);
            ractive.update();
        });

        ractive.on("expandAll", function(event) {
            var questions = ractive.get("questions");
            for(i=0; i<questions.length; i++)
                questions[i].collapsed=false;
            ractive.set("questions",questions);
            ractive.update();
        });

        ractive.on("reorderQuestions", function(context) {
            let question_sortable = Sortable.create(document.getElementById('question_order_list'), {
                animation: 150,
            });
            ractive.set("question_sortable", question_sortable);
        });

        ractive.on("applyQuestionOrder", function(context) {
            let question_sortable = ractive.get("question_sortable");
            let order = question_sortable.toArray();
            console.log(order);

            let questions = ractive.get("questions");
            list =[];
            order.forEach(function(id, index) {
                questions[id].order = index;
            });

            questions.forEach((q, index) => {
                if(q.type===data.MOLECULE_QUESTION) {
                    q.molecule.drawing = Kekule.IO.saveFormatData(data.kekules[index].getChemObj(), 'Kekule-JSON'); //Store the molecule
                    document.getElementById('molecule_'+index).innerHTML=''; //Clear the molecule composer

                }
            });

            questions.sort((a,b) => (a.order > b.order ? 1 : -1));
            console.log(questions);

            ractive.set("questions", questions);
            question_sortable.sort([...Array(question_sortable.toArray().length).keys()]);
            $('#question_order_modal').modal('hide');

            questions.forEach((q,index) => {
                if(q.type === data.MOLECULE_QUESTION)
                    attachKekule(index, q.molecule.drawing);
                questionEditor(index,q.description);
            })

        });


        // Variable Actions
        ractive.on("addVariable", function(event){
            var question = event.get();
            var newHTMLid = "q_" + event.resolve().split('.')[1] + "_var_" + (question.variables.length);
            var newVariable =
                {
                    active: "0",
                    descript: "",
                    id: "-1",
                    name: "",
                    question_id: question.id,
                    shared: "0",
                    title: "",
                    type: 0,
                    choices: [],
                    html_id: newHTMLid
                };
            question.variables.push(newVariable);
            ractive.update();
            $("#" + newHTMLid).focus();
        });

        ractive.on("moveVariable", function (event) {
            var question = getQuestion(event.resolve());
            var variable = event.get();
            var variables = question.variables;
            var index = variables.indexOf(variable);

            if (event.node.id == "down") {
                if (index + 1 < variables.length)
                {
                    console.log("Swapping down");
                    variables[index] = variables[index + 1];
                    variables[index + 1] = variable;
                }
            }
            else {
                if (index - 1 >= 0)
                {
                    console.log("Swapping up");
                    variables[index] = variables[index - 1];
                    variables[index - 1] = variable;
                }
            }

            setQuestion(event.resolve(), question);
        });

        ractive.on("deleteVariable", function(event){
            var question = getQuestion(event.resolve());
            var index = question.variables.indexOf(event.get());
            question.variables.splice(index, 1);
            ractive.update();
        });

        ractive.on("editSelectionVariable", function(context) {
            let variable = context.get();
            variable.pasteJSON = false;
            ractive.set("selectionVariable", variable);
            let selection_sortable = Sortable.create(document.getElementById('selection_list'), {
                animation: 150,
                handle: ".choice-handle",
                onMove:function (evt) {
                    if(evt.from !== evt.to)  //Prevent moving between lists.
                        return false;
                },
                onEnd: function (evt) {
                    let order = this.toArray();
                    console.log(order);

                    let choices = ractive.get("selectionVariable.choices");
                    //let responses = ractive.get("written."+wq+".question.responses");
                    list =[];
                    order.forEach(function(id, index) {
                        choices[id].order = index;
                    });

                    choices.sort((a,b) => (a.order > b.order ? 1 : -1));
                    console.log(choices);

                    ractive.set("selectionVariable.choices", choices);
                    this.sort([...Array(selection_sortable.toArray().length).keys()]);
                }
            });
        });

        ractive.on("addVariableChoice", function(context) {
            let variable = ractive.get("selectionVariable");
            if(variable.choices === null)
                variable.choices = [];
            variable.choices.push({
                name: '',
                value: '',
                order: variable.choices.length,
            });
            ractive.update();
            choiceIndex = variable.choices.length-1;
            document.getElementById('choice_'+choiceIndex+'_value').focus();
        });

        ractive.on("removeVariableChoice", function(context, index) {
            let choices = ractive.get("selectionVariable.choices");
            choices.splice(index,1);
            choices.forEach((choice,i) => {choice.order = i});
            ractive.update();
        });

        ractive.on("copySelectionJSON", function(context) {
            let variable = ractive.get("selectionVariable");
            let text = JSON.stringify(variable.choices);
            let dummy = document.createElement("textarea");
            document.getElementById('selection_variable_modal').appendChild(dummy);
            dummy.value = text;
            dummy.select();
            document.execCommand("copy");
            document.getElementById('selection_variable_modal').removeChild(dummy);
        });

        ractive.on("pasteSelectionJSON", function(context) {
            ractive.set("selectionVariable.pasted_JSON","");
            this.toggle('selectionVariable.pasteJSON');
            ractive.update();
            document.getElementById("paste_selection_JSON_textarea").focus();
        });

        ractive.on("importSelectionJSON", function(context) {
            let imported_JSON = ractive.get("selectionVariable.pasted_JSON");
            let newChoices = JSON.parse(imported_JSON);
            let variable = ractive.get("selectionVariable");
            let orderIndex = variable.choices.length;
            newChoices.forEach((choice,i) => {choice.order = orderIndex + i});
            variable.choices = variable.choices.concat(newChoices);
            variable.pasteJSON = false;
            ractive.set("selectionVariable",variable);
            ractive.update();
        });


        // Inter Actions
        ractive.on("addIter", function(event){
            var question = event.get();
            console.log(event.get());
            var newHTMLid = "q_" + event.resolve().split('.')[1] + "_intervar_" + (question.inter_variables.length);
            var newIter =
                {
                    id: "-1",
                    name: "",
                    equation: "",
                    question_id: question.id,
                    html_id: newHTMLid
                };
            question.inter_variables.push(newIter);
            ractive.update();
            document.getElementById("paste_selection_JSON_textarea").focus();
        });

        ractive.on("deleteIter", function(event){

            var question = getQuestion(event.resolve());
            var index = question.inter_variables.indexOf(event.get());
            question.inter_variables.splice(index, 1);
            ractive.update();
        });

        ractive.on("moveInter", function(event){
            var question = getQuestion(event.resolve());
            var inter = event.get();
            var inters = question.inter_variables;
            var index = inters.indexOf(inter);

            if (event.node.id == "Down")
            {
                if (index + 1 < inters.length)
                {
                    console.log("Swapping down");
                    inters[index] = inters[index + 1];
                    inters[index + 1] = inter;
                }
            }
            else
            {
                if (index - 1 >= 0)
                {
                    console.log("Swapping up");
                    inters[index] = inters[index - 1];
                    inters[index - 1] = inter;
                }
            }

            setQuestion(event.resolve(), question);
        });


        // Condition Actions
        ractive.on("addCondition", function(event){
            console.log(event);
            var question = event.get();
            var newHTMLid = "q_" + event.resolve().split('.')[1] + "_cond_" + (question.conditions.length - 1);
            var newCond =
                {
                    id: "-1",
                    name: "",
                    equation: "",
                    points: "0",
                    question_id: question.id,
                    result: "",
                    type: "1",
                    html_id: newHTMLid
                };
            question.conditions.splice(question.conditions.length - 1, 0, newCond);
            ractive.update();
            $("#" + newHTMLid).focus();
        });

        ractive.on("deleteCondition", function(event){
            var question = getQuestion(event.resolve());
            var index = question.conditions.indexOf(event.get());
            question.conditions.splice(index, 1);
            ractive.update();
        });

        ractive.on("moveCondition", function(event){
            var question = getQuestion(event.resolve());
            var condition = event.get();
            var conditions = question.conditions;
            var index = conditions.indexOf(condition);

            if (event.node.id == "Down")
            {
                if (index + 1 < conditions.length)
                {
                    console.log("Swapping down");
                    conditions[index] = conditions[index + 1];
                    conditions[index + 1] = condition;
                }
            }
            else
            {
                if (index - 1 >= 0)
                {
                    console.log("Swapping up");
                    conditions[index] = conditions[index - 1];
                    conditions[index - 1] = condition;
                }
            }

            setQuestion(event.resolve(), question);
        });

        // Response Actions
        ractive.on("addResponse", function(event){
            var question = event.get();
            var newHTMLid = "q_" + event.resolve().split('.')[1] + "_resp_" + (question.responses.length - 1);
            var newResponse =
                {
                    id: "-1",
                    response: "",
                    html_id: newHTMLid
                };
            question.responses.push(newResponse);
            ractive.update();
            $("#" + newHTMLid).focus();
        });

        ractive.on("deleteResponse", function(event){
            var question = getQuestion(event.resolve());
            var index = question.responses.indexOf(event.get());
            question.responses.splice(index, 1);
            ractive.update();
        });

        ractive.on("inline_discussion", function(event) {
            ractive.toggle("assignment.options.inline_discussion");
        });

        ractive.on("toggle_mode", function(event) {
            let type = this.get("assignment.type");
            if(type == null || type === 1) {
                type = 2;
            }
            else
                type = 1;
            this.set("assignment.type",type);
        });

        ractive.on("show_extra", function(event) {
            ractive.toggle("assignment.options.showExtraEditors");
        });

        ractive.on("new_values", function(event) {
            var question = event.get();
            question.extra.newValues  = !question.extra.newValues;
            ractive.update();
        });

        ractive.on("addMCChoice", function(context,qind, cind=null) {
            let question;
            if(context.event.type == "keydown") {
                question = context.getParent().getParent().get();
                console.log(context.event.key);
                if(context.event.key !== "Enter") {
                    shiftChoice(context.event.key, question.choices.length, qind, cind);
                    return;
                }
            }
            else
                question = context.get();
            let choices = question.choices;
            choices.push({
                id: getNextChoiceID(choices),
                description: '',
            });
            ractive.update();
            console.log(context.resolve());
            document.getElementById("question_"+qind+"_choice_"+(choices.length-1)+"_description").focus();
        });

        function shiftChoice(key, choices, qind, cind) {
            let newc;
            if(key === "ArrowDown")
                newc = cind+1;
            else if(key === "ArrowUp")
                newc = cind-1;
            else
                return;
            if(newc < 0 || newc > (choices.length-1))
                return;
            document.getElementById("question_"+qind+"_choice_"+newc+"_description").focus();
        }

        ractive.on("removeMCChoice", function(context, index) {
            let choices = context.getParent().get();
            let question = context.getParent().getParent().get();
            console.log(question);
            //Remove from answers list if it was included
            let answer = question.answer;
            let answer_array;
            answer_array = answer === '' ? [] : answer.toString().split(',');
            let answer_index = answer_array.indexOf(choices[index].id.toString());
            if(answer_index > -1) {
                answer_array.splice(answer_index, 1);
                question.answer = answer_array.toString();
            }

            //Remove choice
            choices.splice(index, 1);
            ractive.update();
        });

        ractive.on("selectMCChoice", function(context, id) {
            let choice = context.get();
            let question = context.getParent().getParent().get();
            let answer = question.answer;
            id = id.toString();
            if(question.options.MC.type==="single") {
                if(answer.toString() === id)
                    answer = '';
                else
                    answer = id;
            }
            else if(question.options.MC.type==="multiple") {
                let answer_array;
                answer_array = answer === '' ? [] : answer.toString().split(',');
                let index = answer_array.indexOf(id);
                if(index > -1)
                    answer_array.splice(index, 1);
                else
                    answer_array.push(id);
                answer = answer_array.toString();
            }
            question.answer = answer;
            ractive.update();
        });

        function getNextChoiceID(choices) {
            if (choices.length === 0)
                return 0;
            return Math.max.apply(Math, choices.map(function(o) { return o.id; }))+1;
        }

        ractive.on("addReactionReactant", function(context) {
            let question = context.get();
            let answer = question.answer;
            if(answer === "")
                answer = {};
            if(answer.reactants === undefined || answer.reactants == null)
                answer.reactants = [];
            answer.reactants.push({
                formulaType: 'Exact',
                phase: null,
                charge: null,
                coefficient: null,
            });
            question.answer = answer;
            ractive.update();
        });

        ractive.on("addReactionProduct", function(context) {
            let question = context.get();
            let answer = question.answer;
            if(answer === "")
                answer = {};
            if(answer.products === undefined || answer.products == null)
                answer.products = [];
            answer.products.push({
                formulaType: 'Exact',
                phase: null,
                charge: null,
                coefficient: null,
            });
            question.answer = answer;
            ractive.update();
        });

        ractive.on("removeSpecies", function(context, q) {
            let keypath = context.resolve();
            let arr = keypath.slice(0, keypath.lastIndexOf('.'));
            let ind = keypath.slice(keypath.lastIndexOf('.') + 1);
            this.splice(arr,ind,1);
            preview_reaction(q);
        });

        ractive.on("reaction_preview", function(context, q) {
            preview_reaction(q);
        });

        ractive.on("formula_charge", function(context,q,v,charge) {
            let variable = context.get();
            console.log(variable);
            variable.charge = charge;
            ractive.update();
            preview_reaction(q);
        });

        ractive.on("formula_phase", function(context,q,v,phase) {
            let variable = context.get();
            console.log(variable);
            variable.phase = phase;
            ractive.update();
            preview_reaction(q);
        });

        ractive.on("formula_type", function(context,type) {
            let species = context.get();
            species.formulaType = type;
            ractive.update();
        });

        function preview_reaction(q) {
            console.log("preview",q);
            let question = ractive.get("questions."+q);
            console.log(question.answer);
            let preview = parse_reaction(question.answer);
            console.log(preview);
            let div = '#q_'+q+'_reaction_preview';
            console.log(div, $(div).html());
            $(div).html(preview);
            MathJax.Hub.Queue(["Typeset",MathJax.Hub,div]);
        }

        function parse_reaction(reaction) {
            let str = "$\\ce{";
            if(reaction.reactants !== undefined)
                reaction.reactants.forEach((r, i) => {
                    str += parse_formula(r);
                    if(i<(reaction.reactants.length-1))
                        str += ' + ';
                });
            if(reaction.products !== undefined) {
                str += ' -> ';
                reaction.products.forEach((p, i) => {
                    str += parse_formula(p);
                    if(i<(reaction.products.length-1))
                        str += ' + ';
                });
            }
            str +="}$";
            return str;
        }

        function parse_formula(species) {
            let str = '';
            let formula = species.formula;
            let coefficient = species.coefficient == null ? '' : species.coefficient == 1 ? '' : species.coefficient;
            let charge = species.charge == null ? '' : species.charge;
            let phase = species.phase == null ? '' : species.phase;
            str = formula === '' ? '' : coefficient + formula + '^{' + charge + '} '  + phase;
            return str;
        }

        function initMCSortable(q_index) {
            let MC_sortable = Sortable.create(document.getElementById('question_'+q_index+'_choices_list'), {
                animation: 150,
                handle: ".choice-handle",
                onMove:function (evt) {
                    if(evt.from !== evt.to)  //Prevent moving between lists.
                        return false;
                },
                onEnd: function (evt) {
                    let order = this.toArray();
                    let choices = ractive.get('questions.'+q_index+'.choices');

                    order.forEach(function(id, index) {
                        choices[id].order = index;
                    });

                    choices.sort((a,b) => (a.order > b.order ? 1 : -1));
                    choices = choices.map(({order,...choice}) => choice); //Drop the order property

                    ractive.set('questions.'+q_index+'.choices', choices);
                    this.sort([...Array(MC_sortable.toArray().length).keys()]);

                }
            });
        }

        function updateQuestionIDs(ids) {
            var questions = ractive.get("questions");
            for(var i=0; i<questions.length; i++) {
                questions[i].id = ids[i].qid;
                if(questions[i].type===data.STANDARD_QUESTION) {
                    questions[i] = updateSubIDs(questions[i],ids[i].subIDs);
                }
            }
            ractive.set("questions",questions);
        }

        function updateSubIDs(question, ids) {
            for(var i=0; i<question.variables.length; i++)
                question.variables[i].id = ids.vIDs[i];
            for(i=0; i<question.inter_variables.length; i++)
                question.inter_variables[i].id = ids.iIDs[i];
            for(i=0; i<question.conditions.length; i++)
                question.conditions[i].id = ids.cIDs[i];
            return question;

        }

        ractive.on("save", function(event){
            ractive.set("question_sortable",null); //Stored sortable causes problem with JSON.stringify.
            ractive.set("errorMsgs", []);
            ractive.set("successMsgs", []);
            getMolecules();
            var data = ractive.get();
            data.kekules = null;
            var assignment = ractive.get('assignment');
            console.log("data prior to submission:");
            console.log(data);
            console.log(JSON.stringify(data));

            $.post(POST_PATH, {_token: "{{ csrf_token() }}", data: JSON.stringify(data)},
                function (response) // POST Request callback
                {
                    try
                    {
                        console.log("Response:");
                        var response = JSON.parse(response);
                        console.log(response);
                        ractive.set('assignment.id', response.assignmentID);
                        if(response.ids !== undefined && response.ids !== null)
                            updateQuestionIDs(response.ids);
                        ractive.set("errorMsgs", response.errorMsgs);
                        ractive.set("successMsgs", response.successMsgs);
                        $("#response").hide();
                        $("#preview").html(response.message).show();
                        $('html, body').animate({scrollTop: $("#responseMsgs").offset().top}, 'slow');
                    }
                    catch (error)
                    {
                        $("#response").html(response).show();
                        $('html, body').animate({scrollTop: $("#button_card").offset().top}, 'slow');
                        console.log(error);
                    }
                    MathJax.Hub.Typeset();
                }
            );
            return;
        });

        ractive.on("clear_closes_at", function(context) {
            ractive.set("assignment.closes_at", null)
            let pickr = document.querySelector("#closes_at")._flatpickr;
            pickr.setDate(null);
        })


    </script>

@endsection
