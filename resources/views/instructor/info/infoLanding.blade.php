@extends('layouts.instructor')

@section('links')
    <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
@stop

@section('JS')
    <script src="{{ asset('js/ractive.min.js') }}"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.9/flatpickr.min.css" rel="stylesheet" media="all">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.9/flatpickr.min.js"></script>
    <script src='{{ asset('/js/moment.min.js') }}'></script>

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
@endsection

@section('content')

    <div id="target"></div>

    <script id="template" type="text/ractive">
        @include('instructor.info.landingPage')
    </script>

    <script type="text/javascript">
        const saveURL     = "{{url('/instructor/course/'.$course->id.'/info/saveInfo')}}";

        var data = {!! $data !!};

        function startup() {

        }

        data.getInfoIndex = function(id) {
            return data.infos.findIndex(x => parseInt(x.id) == parseInt(id));
        };

        console.log(data);

        var ractive = new Ractive({
            el: "#target",
            template: "#template",
            data: data,
            delimiters: [ '[[', ']]' ],
            computed: {
                unsaved_updates: function() {
                    let infos = this.get("infos");
                    if(this.get("global_update"))
                        return true;
                    return infos.filter(x => x.updated === true).length > 0;
                }
            }
        });

        ractive.set("saving", "Save Course Info");
        ractive.set("save_sch_msg","Save Schedule");

        // Schedules
        var course = ractive.get("course");
        var infos = ractive.get("infos");

        function unsavedSchedules(state) {
            ractive.set("unsavedSchedules", state);
            ractive.set("save_sch_msg", "Save Schedule");
        }

        var calendars = flatpickr("input[data-id='schedule_cal'",
            {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                altInput: true,
                altInputClass: "inline-block",
                altFormat: "F j, Y H:i",
            }
        );

        if(calendars.length==undefined) {  //If there's only one calendar, it's not an array
            calendars.setDate(course.schedules[0].time);
            calendars.config.onChange.push(function(selectedDates, dateStr, instance) {
                var index = instance.input.name;
                course.schedules[index].time=dateStr;
                unsavedSchedules(true);
            });
        }
        //Initialize existing schedules
        for(i=0; i<calendars.length; i++) {
            calendars[i].setDate(course.schedules[i].time);
            calendars[i].config.onChange.push(function(selectedDates, dateStr, instance) {
                var index = instance.input.name;
                course.schedules[index].time=dateStr;
                unsavedSchedules(true);
            });
        }

        var quiz_calendars = flatpickr("input[data-id='quiz_cal'",
            {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                altInput: true,
                altInputClass: "inline-block",
                altFormat: "F j, Y H:i",
            }
        );

        if(quiz_calendars.length==undefined) {  //If there's only one calendar, it's not an array
            var info_index = quiz_calendars.element.getAttribute('info-id');
            var quiz_index = quiz_calendars.element.getAttribute('name');
            quiz_calendars.setDate(infos[info_index].info_quizzes[quiz_index].closed);
            quiz_calendars.config.onChange.push(function(selectedDates, dateStr, instance) {
                var index = instance.input.name;
                var i_index = instance.input.getAttribute('info-id');
                infos[i_index].info_quizzes[index].closed=dateStr;
                infos[i_index].updated = true;
                ractive.update();
            });
        }
        //Initialize existing schedules
        for(i=0; i<quiz_calendars.length; i++) {
            var info_index = quiz_calendars[i].element.getAttribute('info-id');
            var quiz_index = quiz_calendars[i].element.getAttribute('name');
            quiz_calendars[i].setDate(infos[info_index].info_quizzes[quiz_index].closed);
            quiz_calendars[i].config.onChange.push(function(selectedDates, dateStr, instance) {
                var index = instance.input.name;
                var i_index = instance.input.getAttribute('info-id');
                infos[i_index].info_quizzes[index].closed=dateStr;
                infos[i_index].updated = true;
                ractive.update();
            });
        }

        ractive.on("add_schedule", function(event) {
            var course = ractive.get("course");
            var infos = ractive.get("infos");
            var new_schedule = {
                id : -1,
                type: "info",
                course_id: course.id,
                details: {
                    info_id: infos[0].id,
                    property: "Active",
                    state: 1,
                },
                completed: 0,
                enabled: 1,
                time: moment(moment.now()).format("YYYY-MM-DD H:mm"),
            };
            unsavedSchedules(true);
            course.schedules.push(new_schedule);
            ractive.update();
            var loc = course.schedules.length-1;
            var pickr = flatpickr("#time"+loc,
                {
                    enableTime: true,
                    defaultDate: new_schedule.time,
                    dateFormat: "Y-m-d H:i",
                    altInput: true,
                    altInputClass: "inline-block",
                    altFormat: "F j, Y H:i",
                    onChange: function(selectedDates, dateStr, instance) {
                        console.log(course.id+ " " + loc + " change date to " + dateStr);
                        course.schedules[loc].time=dateStr;
                    }
                });
            console.log(pickr);
        });

        ractive.on("remove_schedule", function(event) {
            var sched = event.get();
            sched.deleted=true;
            unsavedSchedules(true);
            //Don't actually delete the item from the array to avoid problems with the onchange event for the picker.  Mark it deleted so it can be removed from the database.
            ractive.update();
        });

        ractive.on("re-enable_schedule", function(event) {
            var sched = event.get();
            sched.completed=0;
            sched.enabled=1;
            unsavedSchedules(true);
            ractive.update();
        });

        ractive.on("schedule_info", function(event) {
            var sched = event.getParent().getParent().get();
            sched.details.info_id = event.node.id;
            console.log(sched);
            unsavedSchedules(true);
            ractive.update();
        });

        ractive.on("schedule_property", function(event) {
            var sched = event.get();
            sched.details.property = event.node.id;
            console.log(sched);
            unsavedSchedules(true);
            ractive.update();
        });

        ractive.on("schedule_state", function(event) {
            var sched = event.get();
            sched.details.state = (event.node.id === 'true' ? 1 : 0 );
            console.log(sched);
            unsavedSchedules(true);
            ractive.update();
        });

        ractive.on("save_schedule", function(event) {
            ractive.set("save_sch_msg","Saving...");
            var schedules = ractive.get("course.schedules");
            console.log(schedules);
            if(schedules.length === 0) {
                console.log("no events");
                return;
            }

            $.post('saveSchedules',
                {
                    _token: "{{ csrf_token() }}",
                    schedules: schedules,
                })
                .done(function (response) {
                    response = JSON.parse(response);
                    console.log(response);
                    ractive.set("course.schedules",response);
                    ractive.set("save_sch_msg","Saved");
                    ractive.set("unsavedSchedules",false);
                    ractive.update();
                })
                .fail(function(xhr,status,error) {  //Deal with post failure.
                    ractive.set("save_sch_msg","Error Saving");
                    console.log(error);
                });
        });

        //Editors

        for(i = 0; i < data.infos.length; i++) {
            infoEditor(i,data.infos[i].text.description)
        }

        function infoEditor(id, text) {
            var div = '#description_' + (id);
            console.log("init " + div);
            $(div).summernote({
                placeholder: 'Topic Information.',
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
                        summernoteOnImageUpload(files,div);
                    },
                    onChange: function(contents, $editable) {
                        //Don't mark updated on init.
                        if(contents !== ractive.get("infos["+id+"].text.description"))
                            ractive.set("infos["+id+"].updated", true);
                        ractive.set("infos["+id+"].text.description", contents);
                    },
                }
            });
            $(div).summernote('code',text);
            //Add event listener to save cursor location on defocus
            $(div).on('summernote.keydown', function(we, e) {
                $(div).summernote('saveRange');
            });
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

        ractive.on("addFile", function(event) {
            var index = event.node.getAttribute('data-id');

            //Workaround for getting back to cursor focus.  See https://github.com/summernote/summernote/issues/1520#issuecomment-474966703
            $('#description_'+(index)).summernote('restoreRange');
            /*$('#description_'+(index)).summernote('createLink', {
                text: event.node.getAttribute('name'),
                url: "{{url('course/'.$course->id.'/files/download')}}"+"/"+event.node.id,
                isNewWindow: false,
            }); */
            $('#description_'+(index)).summernote('insertText', '#!'+event.node.getAttribute('name')+'!#');
            $('#description_'+(index)).summernote('saveRange');
        });

        ractive.on("addAssignment", function(event) {
            var index = event.node.getAttribute('data-id');

            //Workaround for getting back to cursor focus.  See https://github.com/summernote/summernote/issues/1520#issuecomment-474966703
            $('#description_'+(index)).summernote('restoreRange');
            $('#description_'+(index)).summernote('insertText', '#~'+event.node.getAttribute('name')+'~#');
            $('#description_'+(index)).summernote('saveRange');
        });

        ractive.on("selectFolder", function(event) {
            var folder = event.get();
            var info = event.getParent().getParent().get();
            info.selected = folder.id;
            ractive.update();
            /*var folders = ractive.get("folders");
            for(i=0; i<folders.length; i++) {
                folders[i].selected = false;
            }
            var folder = event.get();
            folder.selected = true;
            ractive.update(); */
        });

        ractive.on("addInfo", function(event) {
            var infos = this.get("infos");
            var info = {
                text: {
                    description: "",
                },
                info_quizzes: [],
                id: -1,
                title: "",
                active: false,
                visible: true,
                options: {},
            };
            infos.push(info);
            var qnum = infos.length-1;
            ractive.update();
            infoEditor(qnum);
            tooltips();
        });

        ractive.on("removeInfo", function(event) {
            ractive.splice("infos",event.get('@index'),1);
            ractive.set("global_update",true);
        });

        ractive.on("moveInfo", function(event) {
            var infos = ractive.get("infos");
            var info = event.get();
            var index = infos.indexOf(info);

            if (event.node.id == "Down")
            {
                if (index + 1 < infos.length)
                {
                    console.log("Swapping down");
                    infos[index] = infos[index + 1];
                    infos[index + 1] = info;
                    infos[index].updated = true;
                    infos[index + 1].updated = true;
                    ractive.set('infos', infos);
                    ractive.update();

                    //Destroy the description editors and reinstantiate them.
                    $('#description_' + (index)).summernote('destroy');
                    $('#description_' + (index+1)).summernote('destroy');
                    infoEditor(index, infos[index].text.description);
                    infoEditor(index+1, infos[index+1].text.description);
                }
            }
            else
            {
                if (index - 1 >= 0)
                {
                    console.log("Swapping up");
                    infos[index] = infos[index - 1];
                    infos[index - 1] = info;
                    infos[index].updated = true;
                    infos[index - 1].updated = true;
                    ractive.set('infos', infos);
                    ractive.update();

                    //Destroy the description editors and reinstantiate them.
                    $('#description_' + (index)).summernote('destroy');
                    $('#description_' + (index-1)).summernote('destroy');
                    infoEditor(index, infos[index].text.description);
                    infoEditor(index-1, infos[index-1].text.description);
                }
            }

        });

        ractive.on("collapseInfo", function(event) {
            var infos = ractive.get("infos");
            var info = event.get();
            var index = infos.indexOf(info);
            infos[index].collapsed = true;
            ractive.update();
        });

        ractive.on("expandInfo", function(event) {
            var infos = ractive.get("infos");
            var info = event.get();
            var index = infos.indexOf(info);
            infos[index].collapsed = false;
            ractive.update();
        });

        ractive.on("collapseAll", function(event) {
            var infos = ractive.get("infos");
            for(i=0; i<infos.length; i++)
                infos[i].collapsed=true;
            ractive.update();
        });

        ractive.on("expandAll", function(event) {
            var infos = ractive.get("infos");
            for(i=0; i<infos.length; i++)
                infos[i].collapsed=false;
            ractive.update();
        });

        ractive.on("toggleActive", function(event) {
            var infos = ractive.get("infos");
            var index = event.node.id;
            if(infos[index].active == true)
                infos[index].active = false;
            else {
                for (var i = 0; i < infos.length; i++) {
                    i == index ? infos[i].active = true : infos[i].active = false;
                }
            }
            infos[index].updated = true;
            ractive.update();
        });

        ractive.on("toggleVisible", function(event) {
            event.get().updated = true;
            ractive.toggle(event.resolve()+".visible");
            ractive.update();
        });

        function list_updated(index) {

            ractive.set("infos."+index+".updated",true);
        }

        ractive.on("addQuiz", function(event){
            var info = event.get();
            var quiz = {
                id: -1,
                info_id: info.id,
                visible: 1,
                state: 1,
                description: "",
                info_quiz_questions: [],
                closed: moment(moment.now()).format("YYYY-MM-DD H:mm"),
            };
            info.info_quizzes.push(quiz);
            info.updated = true;
            ractive.update();
            var loc = info.info_quizzes.length-1;
            console.log("#qtime"+loc);
            var pickr = flatpickr("#qtime"+loc,
                {
                    enableTime: true,
                    defaultDate: quiz.closed,
                    dateFormat: "Y-m-d H:i",
                    altInput: true,
                    altInputClass: "inline-block",
                    altFormat: "F j, Y H:i",
                    onChange: function(selectedDates, dateStr, instance) {
                        var index = instance.input.name;
                        var i_index = instance.input.getAttribute('info-id');
                        infos[i_index].info_quizzes[index].closed=dateStr;
                        infos[i_index].updated = true;
                        ractive.update();
                    }
                });
            console.log(pickr);
            ractive.update();
        });

        ractive.on("addQuestion", function(event) {
            let info = event.getParent().getParent().get();
            info.updated = true;
            var info_quiz = event.get();
            var question = {
                id: -1,
                type: 1,
                info_quiz_id: info_quiz.id,
                description: "",
                choices: [],
                options: [],
                answer: {selected: []},
                points: 1,
            };
            info_quiz.info_quiz_questions.push(question);
            ractive.update();
        });

        ractive.on("addQuestionChoice", function(event) {
            let info = event.getParent().getParent().getParent().getParent().get();
            info.updated = true;
            var question = event.get();
            var choice = {
                description: "",
            };
            question.choices.push(choice);
            console.log(question);
            ractive.update();
        });

        ractive.on("setQuestionType", function(event) {
            let info = event.getParent().getParent().getParent().getParent().get();
            info.updated = true;
            var question = event.get();
            question.type = event.node.id;
            ractive.update();
        });

        ractive.on("removeQuizItems", function(event,infoIndex) {
            keypath = event.resolve();
            lastDot = keypath.lastIndexOf('.');
            parent = keypath.substr(0, lastDot);
            index = keypath.substring(lastDot+1);
            ractive.splice(parent,index,1);
            console.log(event.get());
            let info = ractive.get("infos."+infoIndex);
            console.log(infoIndex,info);
            info.updated = true;
            ractive.update();
        });

        ractive.on("select-choice", function(event) {
            keypath = event.resolve();
            lastDot = keypath.lastIndexOf('.');
            index = keypath.substring(lastDot+1);
            var question = event.getParent().getParent().get();
            if(!question.answer.selected.includes(index)) {
                if(question.type == 1)
                    question.answer.selected = [index];
                else if(question.type == 2)
                    question.answer.selected.push(index);
            }
            else
                question.answer.selected.splice(question.answer.selected.indexOf(index),1);
            let info = event.getParent().getParent().getParent().getParent().getParent().getParent().get();
            info.updated = true;
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
                    //Assign ids to everything that was saved.
                    for(i=0; i<data.infos.length; i++) {
                        data.infos[i].id = response.ids[i]['info_id'];
                        data.infos[i].updated = false;
                        for(j=0; j<data.infos[i].info_quizzes.length; j++) {
                            data.infos[i].info_quizzes[j].id = response.ids[i]['quiz_ids'][j]['quiz_id'];
                            for(k=0; k<data.infos[i].info_quizzes[j].info_quiz_questions.length; k++) {
                                data.infos[i].info_quizzes[j].info_quiz_questions[k].id = response.ids[i]['quiz_ids'][j]['question_ids'][k]['question_id'];
                            }
                        }
                    }
                    ractive.set("global_update",false);
                    ractive.update();
                })
                .fail(function(xhr,status,error) {  //Deal with post failure.  Suggest page reload in case it's a session timeout.
                    ractive.set("saving","Error Saving");
                    console.log(error);
                });
        });

        //Include the name in the upload dialog

        $('#info_import').on('change',function(){
            //get the file name
            var fileName = $(this).val();
            fileName = fileName.replace(/^.*\\/, "");
            //replace the "Choose a file" label
            $(this).next('.custom-file-label').html(fileName);
            console.log(fileName);
        });

        function tooltips() {
            $('[data-toggle="tooltip"]').tooltip();
            $('[data-toggle-second="tooltip"]').tooltip();
        }
        tooltips();

    </script>
@stop
