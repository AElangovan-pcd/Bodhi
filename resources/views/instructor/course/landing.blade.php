@extends('layouts.instructor')

@section('JS')
    <script src="{{ asset('js/ractive.min.js') }}"></script>
    <script src="{{ asset('js/Sortable.min.js') }}"></script>
    <script src='{{ asset('/js/moment.min.js') }}'></script>

    <link href="{{ asset('css/summernote/summernote-bs4.css') }}" rel="stylesheet">
    <script src="{{ asset('js/summernote-bs4.js') }}"></script>
    <script src="{{ asset('js/exif.js') }}"></script>
    <script src="{{ asset('js/summernote-images.js') }}"></script>
    <script src="{{ asset('js/summernote-image-attributes.js') }}"></script>
    <script src="{{ asset('js/summernote-a11y.js') }}"></script>

@endsection

@section('content')

    <div id="target"></div>

    <script id="template" type="text/ractive">
        @include('instructor.course.landingPage')
    </script>


    <script>
        var data = {!! $data !!};
        console.log(data);

        //Info regex to insert file references
        var file_regex = /#!(.*?)!#/g;
        var assignment_regex = /#~(.*?)~#/g;
        var review_regex = /#r~(.*?)~r#/g;
        for(i=0; i<data.infos.length; i++) {
            if(data.infos[i].active)
                data.activeInfo = i;
            data.infos[i].text.description = update_dynamic_links(data.infos[i].text.description);
        }

        data = get_progress(data);
        data = set_closes(data);

        function get_progress(data) {
            for (let i = 0; i < data.assignments.length; i++) {
                let progress = {};
                let a = data.assignments[i];
                progress.questions = a.questions_count;
                progress.results = a.results_count;
                progress.earned = get_earned(a.results);
                progress.available = get_assignment_points(a.questions);
                try {
                    progress.submitPercent = Math.round(progress.results / progress.questions * 100);
                }
                catch {
                    progress.submitPercent = 0;
                }
                try {
                    progress.earnedPercent = Math.round(progress.earned / progress.available * 100);
                }
                catch {
                    progress.earnedPercent = 0;
                }
                progress.difference = progress.earnedPercent - progress.submitPercent;

                progress.display = a.type !== 2 && a.questions_count > 0;
                a.progress = progress;
                data.assignments[i] = a;
            }
            return data;
        }

        function get_earned(results) {
            let earned = 0;
            for(let i=0; i< results.length; i++) {
                if(!isNaN(results[i].earned))
                    earned += results[i].earned;
            }
            return earned;
        }

        function get_assignment_points(questions) {
            if(questions === undefined) return 0;
            let points = 0;
            for(let i=0; i<questions.length; i++) {
                points += questions[i].max_points;
            }
            return points;
        }

        function set_closes(data) {
            for (let i = 0; i < data.assignments.length; i++) {
                let a = data.assignments[i];
                let closes_at = a.closes_at;
                if(closes_at != null) {
                    closes_at = moment(closes_at);
                    let formatted = format_time(closes_at);
                    let set_closes_at = formatted;
                    if(closes_at.diff(data.loaded_time) < 0)
                        a.closed = true;
                    else
                        formatted += ' (' + closes_at.from(data.loaded_time) + ')';
                    if(a.extension != null) {
                        let extension = moment(a.extension.expires_at);
                        formatted += '<br>*Extension granted'
                        if(a.extension.expires_at != null)
                            formatted += ' through ' + format_time(extension) + ' (' + extension.from(data.loaded_time) + ')';
                        if(a.closed && (extension.diff(data.loaded_time) > 0 || a.extension.expires_at == null) ) {
                            a.closed = false;
                            if(extension.diff(data.loaded_time, 'hours') < 24 && extension.diff(data.loaded_time, 'hours') > 0)
                                a.closes_soon = true;
                        }
                    }
                    else {
                        if(closes_at.diff(data.loaded_time, 'hours') < 24 && closes_at.diff(data.loaded_time, 'hours') > 0)
                            a.closes_soon = true;
                        data.assignments[i] = a;
                    }
                    a.closes_at = formatted;

                }
            }
            return data;
        }

        function format_time(str) {
            return moment(str).format('dddd MMMM Do YYYY, h:mm a');
        }

        data.course.descriptionDisplay = update_dynamic_links(data.course.description);

        function update_dynamic_links(item) {
            if (item == null)
                return item;
            item = item.replace(file_regex, function(match, p1) {
                if(data.fileList[p1] == undefined)
                    return p1;
                let link = '<a href="files/download/'+data.fileList[p1]+'">'+p1+'</a>';
                return link;
            });
            item = item.replace(assignment_regex, function(match, p1) {
                console.log(p1);
                if(data.assignmentList[p1] == undefined)
                    return p1;
                let link = '<a href="assignment/'+data.assignmentList[p1]+'/view">'+p1+'</a>';
                return link;
            });
            item = item.replace(review_regex, function(match, p1) {
                console.log(p1);
                if(data.reviewList[p1] == undefined)
                    return p1;
                let link = '<a href="review/'+data.reviewList[p1]+'/view">'+p1+'</a>';
                return link;
            });
            return item;
        }

        var ractive = new Ractive({
            target: '#target',
            template: '#template',
            data: data,
            delimiters: [ '[[', ']]' ],
        });

        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
            $('[data-toggle-second="tooltip"]').tooltip();
        });

        $('#course_description').summernote({
            placeholder: 'Course description students will see.',
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
            popover: {
                image: [
                    ['custom', ['imageAttributes']],
                    ['imagesize', ['resizeFull', 'resizeHalf', 'resizeQuarter', 'resizeNone']],
                    ['float', ['floatLeft', 'floatRight', 'floatNone']],
                    ['remove', ['removeMedia']]
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
                    summernoteOnImageUpload(files,"#course_description");
                },
                onChange: function(contents, $editable) {
                    ractive.set("course.new_description", contents)
                }
            }
        });

        ractive.on("selectActiveInfo", function(context, index) {
            this.set('activeInfo',index);
            MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
        });

        //Quiz logic
        ractive.on("quiz-select", function(event) {
            keypath = event.resolve();
            lastDot = keypath.lastIndexOf('.');
            index = keypath.substring(lastDot+1);
            var question = event.getParent().getParent().get();
            var quiz = event.getParent().getParent().getParent().getParent().get();
            if(question.info_quiz_answers.length === 0)
                question.info_quiz_answers.push({answer:{selected:[]}});
            if(!question.info_quiz_answers[0].answer.selected.includes(index)) {
                if(question.type == 1)
                    question.info_quiz_answers[0].answer.selected = [index];
                else if(question.type == 2)
                    question.info_quiz_answers[0].answer.selected.push(index);
            }
            else
                question.info_quiz_answers[0].answer.selected.splice(question.info_quiz_answers[0].answer.selected.indexOf(index),1);
            quiz.saving = "You have unsaved changes."
            console.log(quiz);
            ractive.update();
        });

        ractive.on("quiz-submit", function(event) {
            var quiz = event.get();
            quiz.saving="Submitting...";
            ractive.update();
            $.post('infoQuizSubmit',
                {
                    _token: "{{ csrf_token() }}",
                    quiz: quiz,
                })
                .done(function (response) {
                    //response = JSON.parse(response);
                    console.log(response);
                    if(response.status==="success")
                        quiz.saving="Submitted!";
                    ractive.update();
                })
                .fail(function(xhr,status,error) {  //Deal with post failure.  Suggest page reload in case it's a session timeout.
                    quiz.saving="Error Submitting";
                    console.log(error);
                    ractive.update();
                });
        });

        function updateTimes() {
            var forum = ractive.get("forum");
            if(forum.last != null)
                forum.last.time = moment(forum.last.created_at).fromNow();
            if(forum.lastResponse != null)
                forum.lastResponse.time = moment(forum.lastResponse.created_at).fromNow();
            var infos = ractive.get("infos");
            var now = moment();
            for (i=0; i< infos.length; i++) {
                for(j=0; j< infos[i].info_quizzes.length; j++) {
                    var verb = "closes ";
                    if(now.isAfter(infos[i].info_quizzes[j].closed)) {
                        infos[i].info_quizzes[j].locked = true;
                        verb = "closed ";
                    }
                    //infos[i].info_quizzes[j].closedRelative = moment(infos[i].info_quizzes[j].closed).fromNow();
                    infos[i].info_quizzes[j].closedFormatted = "Submission " + verb + moment(infos[i].info_quizzes[j].closed).format('MMMM Do YYYY, h:mm a') + " (" + moment(infos[i].info_quizzes[j].closed).fromNow() +"). ";;
                }
            }
            ractive.update();
        }

        //Make the assignment lists sortable
        var activeOrder = [];
        var activeList = ractive.get("assignments");
        for(i=0;i<activeList.length;i++)
            activeOrder.push(activeList[i].id);
        ractive.set("activeOrder", activeOrder);
        activeList = Sortable.create(document.getElementById('assignment_list'), {
            handle: ".sorting-handle",
            onMove:function (evt) {
                if(evt.from !== evt.to)  //Prevent moving between lists.
                    return false;
            },
            onEnd: function (evt) {
                ractive.set("activeOrder", activeList.toArray());
            }
        });



        ractive.on("updateOrder", function(event) {
            var data = {
                _token: "{{ csrf_token() }}",
                active: ractive.get("activeOrder"),
            };
            $.post("{{url('instructor/course/'.$course->id.'/updateOrder')}}", data, function(resp) {
                console.log(resp);
                var n = new Noty({
                    theme: 'bootstrap-v4',
                    type: 'success',
                    layout:'centerRight',
                    text: 'Assignment order updated.  Click to dismiss.',
                }).show();
            });
        });

        ractive.on("selectAssignments", function(event) {
            ractive.toggle("selectAssignments");
        });

        ractive.on("selectAssignment", function(event) {
           let assignment = event.get();
           assignment.selected = !assignment.selected;
           let selected = ractive.get("selectedAssignments") ?? [];
           let idx = selected.indexOf(assignment.id);
           idx === -1 ? selected.push(assignment.id) : selected.splice(idx,1);
           ractive.set("selectedAssignments", selected);
           ractive.update();
        });

        ractive.on("applyToSelected", function(event, action) {
            console.log("Apply");
            let postData = {
                _token: "{{ csrf_token() }}",
                action: action,
                selected: ractive.get("selectedAssignments"),
            };
            ractive.set("applyToSelectedMsg","Wait...");
            $.post("{{url('instructor/course/'.$course->id.'/updateAssignmentStates')}}", postData, function(resp) {
                console.log(resp);
                if(resp.status === 'fail') {
                    ractive.set("applyToSelectedMsg", "Error");
                }
                else if(resp.status === 'success') {
                    ractive.set("applyToSelectedMsg", "Success...reloading")
                    location.reload();
                }
            });
        });

        //Sort function for the student list
        ractive.sort = ractive.sort = function ( column ) {
            var array = Object.values(this.get("students"));

            //If the table is already sorted by this column, reverse it.
            if(column == ractive.get("sortColumn")) {
                array.reverse();
                this.set("students", array);
                return;
            }

            array.sort(function(a, b) {
                if(column == 'seat') {
                    a = a.pivot.seat.toLowerCase();
                    b = b.pivot.seat.toLowerCase();
                }
                else {
                    a = a[column].toLowerCase();
                    b = b[column].toLowerCase();
                }
                return a < b ? -1 : 1;
            });
            this.set("students",array);
            this.set("sortColumn", column);
        };


        var img = new Image();
        var canvas = document.getElementById('classCanvas');

        img.src = "{{ url('instructor/course/'.$course->id.'/classroomImage?'.time()) }}";  //Time is added to the filename to prevent browser from cacheing old image after upload.

        img.onload = function () {

            var dpr = 2;  //Make the canvas too big at first to improve image resolution.
            var rect = canvas.getBoundingClientRect();

            canvas.width = rect.width * dpr;
            canvas.height = canvas.width;//rect.height * dpr;
            var ctx = canvas.getContext('2d');

            ctx.drawImage(img, 0, 0,canvas.width,canvas.height);

            //Scale everything back down to fit.
            canvas.style.width=canvas.parentNode.offsetWidth-40+'px';
            console.log(window.innerHeight);
            console.log(canvas.style.width);
            if (canvas.parentNode.offsetWidth > window.innerHeight)   //Make sure it all fits on a vertical page
                canvas.style.width = window.innerHeight - 40 + 'px';
            canvas.style.height=canvas.style.width;

            addSeats(canvas);
        }

        function addSeats(canvas) {

            var ctx = canvas.getContext('2d');
            ctx.clearRect(0,0,canvas.width,canvas.height);
            ctx.drawImage(img, 0, 0,canvas.width,canvas.height);
            var students = ractive.get("students");
            let seats = ractive.get("course.seats")
            ctx.font = "16px Arial";
            ctx.textAlign = 'center';

            var noLocation = [];

            for(var key in students) {
                let seatIndex = -1;
                if(students[key].pivot.seat != null)
                    seatIndex = seats.findIndex(obj => obj.name.toLowerCase() === students[key].pivot.seat.toLowerCase());
                if(seatIndex == -1)
                    noLocation.push(students[key]);
                else {
                    let seat = seats[seatIndex];
                    ctx.fillText(students[key].firstname, seat.x * canvas.width, seat.y * canvas.height);
                }
            }
            ractive.set("noLocation", noLocation);
            console.log(noLocation);
        }

        window.addEventListener('resize', function(evt) {
            canvas.style.width=canvas.parentNode.offsetWidth-40+'px';
            canvas.style.height=canvas.parentNode.offsetWidth-40+'px';
        });

        updateTimes();
        setInterval(function(){
            updateTimes();
        },60000);

        course_socket
            .listen('NewForumAnswer', (e) => {
                console.log(e);
                var forum = ractive.get("forum");
                console.log(forum);
                forum.lastResponse.id = e.forum_id;
                forum.lastResponse.title = e.forum_title;
                forum.lastResponse.created_at = e.created_at;
                forum.newResponses++;
                console.log(forum);
                ractive.set("forum",forum);
                updateTimes();
                var n = new Noty({
                    theme: 'bootstrap-v4',
                    type: 'information',
                    layout:'bottomRight',
                    text: '<strong>New Forum Response</strong><br>There is a new response in the forum \''+e.forum_title+'\'.',
                    buttons: [
                        Noty.button('Go!', 'btn btn-success mr-2', function () {
                            console.log("load pressed");
                            window.location.href = 'forum/view/' + e.forum_id;
                            n.close();
                        }),
                        Noty.button('Ignore', 'btn btn-danger', function() {
                            console.log("no pressed");
                            n.close();
                        })
                    ]
                }).show();

            })
            .listen('NewForumTopic', (e) => {
                console.log(e);
                var forum = ractive.get("forum");
                console.log(forum);
                forum.last.id = e.forum_id;
                forum.last.title = e.forum_title;
                forum.last.created_at = e.created_at;
                forum.unread++;
                console.log(forum);
                ractive.set("forum",forum);
                updateTimes();
                var n = new Noty({
                    theme: 'bootstrap-v4',
                    type: 'information',
                    layout:'bottomRight',
                    text: '<strong>New Forum Topic</strong><br>\''+e.forum_title+'\'',
                    buttons: [
                        Noty.button('Go!', 'btn btn-success mr-2', function () {
                            console.log("load pressed");
                            window.location.href = 'forum/view/' + e.forum_id;
                            n.close();
                        }),
                        Noty.button('Ignore', 'btn btn-danger', function() {
                            console.log("no pressed");
                            n.close();
                        })
                    ]
                }).show();
            })
        ;

        ractive.on("toggleLinkable", function(context) {
            ractive.set("linkableMsg", "Toggling...");
            $.post('toggleLinkable',
                {
                    _token: "{{ csrf_token() }}",
                })
                .done(function (response) {
                    console.log(response);
                    if(response.status==="success") {
                        ractive.set("course.linkable", response.linkable);
                        ractive.set("linkableMsg",null);
                    }
                    else {
                        ractive.set("linkableMsg", "Error");
                        console.log(response.message);
                    }
                })
                .fail(function(xhr,status,error) {  //Deal with post failure.  Suggest page reload in case it's a session timeout.
                    ractive.set("linkableMsg", "Error");
                    console.log(error);
                    ractive.update();
                });
        });

        ractive.on("makeLinkRequest", function(context, mode, action, other_cid = null, index = null) {
            let requested_index = ractive.get("linkRequestCourse");
            let requested = other_cid !== null ? other_cid : ractive.get("linkableCourses."+requested_index).id;
            let inputs = {
                action: action,
                other_cid: requested,
            };
            let msgLocation;
            if(mode === 'outgoing')
                linkRequests(inputs, "linkRequestMsg", "course.link_requests_outgoing");
            else if(mode === 'incoming')
                linkRequests(inputs, "linkRequestMsgs."+index, "course.link_requests_incoming");
            else if(mode === 'unlink_child')
                linkRequests(inputs, "linkedMsgs."+index,"unlinkMsg");
            else if(mode === 'unlink_parent')
                linkRequests(inputs, "linkedParentMsg","unlinkMsg");
        });

        function linkRequests(inputs, msgLocation, setPoint) {
            console.log(inputs);
            inputs._token = "{{ csrf_token() }}";
            ractive.set(msgLocation, "Submitting...");

            $.post('linkRequest', inputs)
                .done(function (response) {
                    console.log(response);
                    if(response.status==="success") {
                        ractive.set(setPoint, response.msg);
                        ractive.set("course.linked_courses",response.course.linked_courses);
                        ractive.set("course.parent_course_id", response.course.parent_course_id);
                        ractive.set("course.linked_parent_course", response.course.linked_parent_course);
                        ractive.set("course.link_requests_incoming", response.course.link_requests_incoming);
                        ractive.set(msgLocation,null);
                    }
                    else {
                        ractive.set(msgLocation, "Error: " + response.msg);
                    }
                })
                .fail(function(xhr,status,error) {  //Deal with post failure.  Suggest page reload in case it's a session timeout.
                    ractive.set(msgLocation, "Error: "+ error);
                    console.log(error);
                });
        }

        ractive.on("update_details", function(context) {
            ractive.set("update_details_msg", "Submitting...");
            let course = ractive.get("course");
            let submission = {
                _token: "{{ csrf_token() }}",
                course: JSON.stringify(course),
            };
            $.post('updateDetails', submission)
                .done(function (response) {
                    console.log(response);
                    if(response.status==="success") {
                        response.course.descriptionDisplay = update_dynamic_links(response.course.description);
                        ractive.set("course.name", response.course.name);
                        ractive.set("course.key", response.course.key);
                        ractive.set("course.description", response.course.description);
                        ractive.set("course.descriptionDisplay", response.course.descriptionDisplay);
                        ractive.set("course.progress_display", response.course.progress_display);
                        ractive.set("update_details_msg","Updated");
                    }
                    else {
                        ractive.set("update_details_msg", "Error: " + response.msg);
                    }
                })
                .fail(function(xhr,status,error) {  //Deal with post failure.  Suggest page reload in case it's a session timeout.
                    ractive.set("update_details_msg", "Error: "+ error);
                    console.log(error);
                });
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

    </script>


@endsection
