@extends('layouts.instructor')

@section('links')
    @if($instructor == 1)
        <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
        <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/assignment/'.$assignment->id.'/view')}}">View</a></li>
        <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/assignment/'.$assignment->id.'/edit')}}">Edit</a></li>
    @else
        <li class="nav-item"><a class="nav-link" href="{{url('course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
        <li class="nav-item"><a class="nav-link" href="{{url('course/'.$course->id.'/assignment/'.$assignment->id.'/view')}}">View</a></li>
        <li class="nav-item"><a class="nav-link" href="{{url('course/'.$course->id.'/LA/assignment/'.$assignment->id.'/edit')}}">Edit</a></li>
    @endif
@stop

@section('JS')
    <script src="{{ asset('js/ractive.min.js') }}"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.9/flatpickr.min.css" rel="stylesheet" media="all">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.9/flatpickr.min.js"></script>
    <link href="{{ asset('css/summernote/summernote-bs4.css') }}" rel="stylesheet">
    <script src="{{ asset('js/summernote-bs4.js') }}"></script>
    <script src="{{ asset('js/exif.js') }}"></script>
    <script src="{{ asset('js/summernote-images.js') }}"></script>
    <script src="{{ asset('js/summernote-image-attributes.js') }}"></script>
    <script src="{{ asset('js/summernote-a11y.js') }}"></script>
    <script src="{{ asset('js/Sortable.min.js') }}"></script>
    <script src="{{ asset('js/similarity-check.js') }}"></script>
    <script src='{{ asset('/js/moment.min.js') }}'></script>
    <script src="{{ asset('js/ecStat.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.1.0/dist/echarts.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mathjs/9.3.2/math.js" integrity="sha512-Imer9iTeuCPbyZUYNTKsBWsAk3m7n1vOgPsAmw4OlkGSS9qK3/WlJZg7wC/9kL7nUUOyb06AYS8DyYQV7ELEbg==" crossorigin="anonymous"></script>
@stop

@section('CSS')
    <style>
        .response-box {
            position: relative;
            height: 200px;
            overflow-x: auto;
            border: 1px solid #ccc;
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;
            border-bottom-right-radius: 5px;
            border-bottom-left-radius: 5px;
            margin-bottom: 4px;
        }

        .response {
            font-size: 12px;
            overflow-x: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        .wide {
            width: 100%;
        }

        .full {
            background-color: hsl(120, 60%, 50%);
        }
        .half {
            background-color: hsl(120, 60%, 75%);
        }
        .zero {
            background-color: hsl(120, 60%, 90%);
        }

        .wide.full {
            margin-top: 5px;
        }

        .retry {
            background-color: #f0ad4e;
        }

        .gray {
            color: #aaa;
        }

        .no-left {
            padding-left: 0px;
        }
    </style>
@stop

@section('content')

    <div id="target"></div>

    <script id="template" type="text/ractive">
        @include('instructor.results.newPage')
    </script>

    <script type="text/javascript">

        var assignment = {!! $assignment !!}

        const postURL = "writtenAnswers/submit_feedback";
        const responsesURL = "writtenAnswers/save_responses";
        const optionsURL = "writtenAnswers/save_options";
        const quizDetailURL = "update_student_quiz_detail";
        const quizBatchDetailURL = "update_batch_quiz_detail";
        const quizDetailStatus = "update_student_quiz_status";
        const quizDetailPageStatus = "update_student_quiz_page_status";
        const quizSettingsURL = "update_quiz_settings";
        const quizDetailToggleReview = "toggle_quiz_review_for_student";
        const quizDetailDeferred = "update_deferred_state_for_student";
        const extensionURL = "../update_extension";

        window.Echo.private('course-instructor.{{$course->id}}')
            .listen('QuestionAnswered', (e) => {
                console.log(e);
                if(e.assignment_id === data.assignment.id)
                    updateResults(e);
            });

        function updateResults(event) {
            let students = ractive.get("students");
            let questions = ractive.get("assignment.questions");
            let sindex = students.findIndex(obj => obj.id === event.user.id);
            let qindex = questions.findIndex(obj => obj.id === event.question_id);
            students[sindex].questions[qindex].result = event.result;
            students[sindex].questions[qindex].answers = event.answers;
            if(questions[qindex].type === 2) { //Written question
                let written = ractive.get("written");
                let windex = written.findIndex(obj => obj.question.id === event.question_id);
                let swindex = written[windex].entries.findIndex(obj => obj.student.id === event.user.id);
                let writtenObject = {
                    student: event.user,
                    answer: event.answers[0],
                    result: event.result
                }
                if(swindex === -1)
                    written[windex].entries.push(writtenObject);
                else
                    written[windex].entries[swindex] = writtenObject;
            }
            updateTotal(students[sindex]);
            ractive.update();
            $("#row_"+sindex).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100);
            //TODO deal with written
            MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
        }

        function updateAssignmentTotal() {
            let gradedFlagOnly = ractive.get("gradedFlagOnly");
            let questions = ractive.get("assignment.questions");
            let total = 0;
            questions.forEach((q,i) => {
                if(gradedCheck(i))
                    total+=q.max_points;
            });
            ractive.set("assignment_total", total);
        }

        function updateTotals() {
            let students = ractive.get("students");
            students.forEach(s => updateTotal(s));
        }

        function updateTotal(student) {
            let earned = 0;

            for (let i = 0; i < student.questions.length; i++) {   // re-tally earned score
                if (student.questions[i].result != null && gradedCheck(i))
                    earned += student.questions[i].result.earned;
            }

            student.total.earned = earned;
            student.total.fraction = earned/ractive.get("assignment_total")*100;

        }

        function gradedCheck(i) {
            let gradedFlagOnly = ractive.get("gradedFlagOnly");
            if(gradedFlagOnly !== true)
                return true;
            let question = ractive.get("assignment.questions."+i);
            if(question.options != null && question.options.gradedFlag)
                return true;
        }

        var data = {!! $data !!};
        console.log(data);

        data = setup(data);

        //Functions to set up initial data
        function setup(data) {
            if(data.linked) {
                data = merge_linked_questions(data);
                data = merge_linked_quiz_jobs(data);
            }
            data = setup_answers(data);
            if(!data.linked && !data.lazy)
                data = populate_written(data);
            if(data.lazy)
                written_question_list(data);
            data = check_for_extensions(data);
            return data;
        }

        function setup_answers(data) {
            let students = data.students;
            let questions = data.assignment.questions;
            students.forEach(function(student) {
                student.total = {'earned' : 0};
                if(student.cached_answer != null)
                    student.cached_answer.values = JSON.parse(student.cached_answer.values);
                student.questions = [];
                questions.forEach(function(question, qi) {
                    student.questions[qi] = {};
                    student.questions[qi].result = question.results.find(obj => obj.user_id === student.id);
                    if(student.questions[qi].result !== undefined)
                        student.total.earned += student.questions[qi].result.earned;
                    if(!data.linked && !data.lazy) {
                        let answers = question.answers.filter(item => item.user_id === student.id);
                        let keyedanswers = [];
                        for (let i = 0; i < answers.length; i++) {
                            if (answers[i].variable_id !== null)
                                keyedanswers[answers[i].variable_id] = answers[i];
                        }
                        if (keyedanswers.length === 0)
                            keyedanswers = answers;
                        student.questions[qi].answers = keyedanswers;
                    }
                    student.total.fraction = data.assignment_total === 0 ? 100 : student.total.fraction = student.total.earned / data.assignment_total * 100;
                });
            });
            return data;
        }

        function setup_student_answers(student, loaded_answers) {
            let questions = ractive.get("assignment.questions");
            questions.forEach(function(question, qi) {
                let answers = loaded_answers.filter(a=>(a.question_id === question.id && a.user_id === student.id));
                let keyedanswers = [];
                for (let i = 0; i < answers.length; i++) {
                    if (answers[i].variable_id !== null)
                        keyedanswers[answers[i].variable_id] = answers[i];
                }
                if (keyedanswers.length === 0)
                    keyedanswers = answers;
                student.questions[qi].answers = keyedanswers;
            });
        }

        function setup_loaded_answers(loaded_answers) {
            let questions = ractive.get("assignment.questions");
            let students = ractive.get("students");
            students.forEach(student => setup_student_answers(student, loaded_answers));
        }

        function put_answers_in_table(qi) {
            let students = ractive.get("students");
            let questions = ractive.get("assignment.questions");
            let question = questions[qi];
            students.forEach(function(student) {
                let answers = question.answers.filter(item => item.user_id === student.id);
                let keyedanswers = [];
                for (let i = 0; i < answers.length; i++) {
                    if (answers[i].variable_id !== null)
                        keyedanswers[answers[i].variable_id] = answers[i];
                }
                if (keyedanswers.length === 0)
                    keyedanswers = answers;
                student.questions[qi].answers = keyedanswers;
            });
            ractive.update();
        }

        function merge_linked_questions(data) {
            let questions = data.assignment.questions;
            let linked_assignments = data.linked_assignments;
            linked_assignments.forEach(function(assignment) {
                assignment.questions.forEach(function(linked_question) {
                    let primary_question_id = questions.findIndex(q => q.id === linked_question.parent_question_id);
                    questions[primary_question_id].results = questions[primary_question_id].results.concat(linked_question.results);
                    questions[primary_question_id].answers = questions[primary_question_id].answers.concat(linked_question.answers);
                })
            });
            return data;
        }

        function merge_linked_quiz_jobs(data) {
            let assignment = data.assignment;
            let linked_assignments = data.linked_assignments;
            linked_assignments.forEach(function(linked) {
                assignment.quiz_jobs = assignment.quiz_jobs.concat(linked.quiz_jobs);
            });
            return data;
        }

        function written_question_list(data) {
            let questions = data.assignment.questions;
            data.written = [];
            questions.forEach(q=> {
                if(q.type===2)
                    data.written.push({question: q, entries: []});
            });
            return data;
        }

        function populate_written(data) {
            let students = data.students;
            let questions = data.assignment.questions;
            data.written = [];
            questions.forEach(function(question) {
                if(question.type !== 2)
                    return;
                entries = [];
                question.answers.forEach(function(answer) {
                    let entry = {
                        student: students.find(obj => obj.id === answer.user_id),
                        answer: answer,
                        result: question.results.find(obj => obj.user_id === answer.user_id),
                    }
                    entries.push(entry);
                });
                //Sort the answers initially by updated_at (oldest first)
                entries.sort(function(a,b) {
                    a = a.answer.updated_at;
                    b = b.answer.updated_at;
                    return a < b ? -1 : 1;
                });
                data.written.push({question: question, entries: entries});
            });

            return data;
        }

        function setup_written_entries(qind, wq, answers) {
            let students = ractive.get("students");
            let question = ractive.get("assignment.questions."+qind);
            entries = [];
            answers.forEach(answer => {
                let entry = {
                    student: students.find(obj => obj.id === answer.user_id),
                    answer: answer,
                    result: question.results.find(obj => obj.user_id === answer.user_id),
                };
                entries.push(entry);
                try {
                    entry.student.questions[qind].answers = [answer];
                }
                catch(e) {
                    console.log("Error setting up written entry: ", e);
                }
            });
            //Sort the answers initially by updated_at (oldest first)
            entries.sort(function(a,b) {
                a = a.answer.updated_at;
                b = b.answer.updated_at;
                return a < b ? -1 : 1;
            });
            let written = ractive.get("written");
            written[wq].entries = entries;
            ractive.set("written", written);
            $("img").addClass("img-fluid")  //Adds class="img-fluid" to any <img> tag to keep images inside container when grading
        }





        function check_for_extensions(data) {
            let students = data.students;
            let extensions = data.assignment.extensions;
            if(extensions === undefined)
                return data;
            extensions.forEach(a => {
                let si = students.findIndex(s => s.id === a.user_id);
                if(si !== -1)
                    students[si].extension = a;
            });

            return data;
        }

        data.csv_text = "";
        data.saveResponseMsg = "Save";
        data.saveOptionsMsg = "Save Options";
        data.releaseMsg = "Release Deferred";

        data.getStudentIndex = function(id) {
            return ractive.get("students").findIndex(obj => obj.id === id);
        };

        data.getStudent = function(id) {
            let students = ractive.get("students");
            return students[students.findIndex(obj => obj.id === id)];
        };

        data.unGradedCount = function(index) {
            let entries = ractive.get("written."+index+".entries");
            let count = entries.filter(obj => obj.result.status === 0).length;
            return count;
        };

        data.getQuestionName = function(id) {
            questions = ractive.get("assignment.questions");
            q = questions.find(x => x.id === id);
            return q.name;
        };

        var ractive = new Ractive ({
            template: '#template',
            el: "#target",
            data: data,
            delimiters: [ '[[', ']]' ],
            computed: {
                /*unGraded: function() {
                    let written = this.get("written");
                    let counts = [];
                    for(let i=0; i<written.length; i++) {
                        let entries = written[i].entries;
                        counts.push(entries.filter(obj => obj.result.status === 0).length);
                    }
                    counts.push(counts.reduce((a, b) => a + b, 0)); //Put the total in the last entry
                    return counts;
                },*/
                unGraded: function() {
                    let questions = this.get("assignment.questions");
                    let counts = [];
                    writtenQuestions = questions.filter(q=>q.type===2);
                    writtenQuestions.forEach(q => {
                        counts.push(q.results.filter(r=>r.status===0).length);
                    });
                    counts.push(counts.reduce((a, b) => a + b, 0)); //Put the total in the last entry
                    return counts;
                },
                unGradedwq: function() {
                    let written = this.get("written");
                    let counts = [];
                    written.forEach(q => {
                        counts.push(q.entries.filter(e=>e.result.status===0).length);
                    });
                    return counts;
                },
                writtenQuestionCount: function() {
                    let questions = this.get("assignment.questions");
                    return questions.filter(q=>q.type===2).length;
                },
                quizSettingsComplete: function() {
                    let type = this.get("assignment.type");
                    if(type!==2)
                        return false;
                    console.log("pass");
                    let options = this.get("assignment.options.quiz");
                    return (options.allowed_start !== undefined && options.allowed_end !== undefined && options.allowed_length !== undefined && options.instructions !==undefined);
                },
                contains_deferred: function() {
                    let questions = this.get("assignment.questions");
                    return questions.filter(question => question.deferred === 1).length > 0;
                },
                stats: function() {
                    let students = this.get("students");
                    students = students.filter(s => !s.instructor);
                    let list = students.map(s => s.total.earned);
                    let zero_length = list.length;
                    list = list.filter(x => x);
                    let bins = list.length === 0 ? [] : ecStat.histogram(list);
                    let mean = list.length > 0 ? math.round(math.mean(list),2) : 0;
                    let stdev = list.length > 0 ? math.round(math.std(list),2) : 0;
                    let median = list.length > 0 ? math.round(math.median(list),2) : 0;
                    let max = list.length > 0 ? math.round(math.max(list),2) : 0 ;
                    let min = list.length > 0 ? math.round(math.min(list),2) : 0;
                    let excluded = zero_length - list.length;
                    let basics = {num: list.length, mean: mean, stdev: stdev, median: median, max: max, min: min, excluded: excluded};
                    let stats = {list: list, bins: bins, basics: basics};
                    return stats;
                }
            }
        });

        ractive.on("view_stats", function(context) {
            $("#stats_modal").modal('show');
            initHistograms();
        });

        var chart;

        function initHistograms() {
            let stats = ractive.get("stats");

            let options = {
                color: ['rgb(25, 183, 207)'],
                grid: {
                    left: '5%',
                    right: '3%',
                    bottom: '10%',
                    top: '3%',
                    containLabel: true
                },
                xAxis: [{
                    type: 'value',
                    scale: true,
                    name: 'Score',
                    nameLocation: 'middle',
                    nameGap: 20
                }],
                yAxis: [{
                    type: 'value',
                    name: 'Count',
                    nameLocation: 'middle',
                    nameGap: 20
                }],
                tooltip: {
                    formatter: function(params) {
                        return `Score Range: ${params.value[4]}<br />
                                Count: : ${params.data[1]} `;
                    }
                },
                series: [{
                    name: 'count',
                    type: 'bar',
                    barWidth: '99.3%',
                    label: {
                        normal: {
                            show: true,
                            position: 'insideTop',
                            formatter: function(params) {
                                return params.value[1];
                            }
                        }
                    },

                }]
            };

            chart = echarts.init(document.getElementById('stats_chart'));
            options.series[0].data = stats.bins.data;
            chart.setOption(options);
            setTimeout(function () {
                // Resize charts
                chart.resize();
            }, 200);
        }

        ractive.on('init_response_modal', function(context) {
            let wq = ractive.get("wq");
            let response_sortable = Sortable.create(document.getElementById('response_list_'+wq), {
                animation: 150,
                handle: ".response-handle",
                onMove:function (evt) {
                    if(evt.from !== evt.to)  //Prevent moving between lists.
                        return false;
                },
                onEnd: function (evt) {
                    let order = this.toArray();
                    console.log(order);

                    let responses = ractive.get("written."+wq+".question.responses");
                    list =[];
                    order.forEach(function(id, index) {
                        responses[id].order = index;
                    });

                    responses.sort((a,b) => (a.order > b.order ? 1 : -1));
                    console.log(responses);

                    ractive.set("written."+wq+".question.responses", responses);
                    this.sort([...Array(response_sortable.toArray().length).keys()]);
                }
            });
            ractive.set("response_sortable."+wq, response_sortable);
        });


        ractive.on('tooltip', function(context) {
            $(function () {
                $('[data-toggle="tooltip"]').tooltip()
            });
        });

        function tooltip() {
            $(function () {
                $('[data-toggle="tooltip"]').tooltip()
            });
        }

        ractive.on("show_attempts", function(event) {
            ractive.toggle('show_attempts');
        });

        ractive.on("toggle_graded_flag", function(context) {
            ractive.toggle("gradedFlagOnly");
            updateAssignmentTotal();
            updateTotals();
            ractive.update();
        });

        ractive.on("show_detail", function (event) {
            console.log(event);
            var student = ractive.get("students")[event.node.id];
            console.log(student);

            if(ractive.get("lazy") && !student.loaded)
                load_student_values(student);

            let questions = ractive.get("assignment.questions");

            if(student.extension === undefined) {
                student.extension = {
                    lock_message: "You were not present for this activity."
                }
            }

            var detail = {
                student: student,
                questions: questions,
                extensionMsg: null,
            };
            console.log(detail);

            seed_detail_calendars(detail);

            ractive.set("detail", detail);
            ractive.update();
            $("#detail_modal").modal('show');
            tooltip();
        });

        ractive.on("update_extension", function(context, allow, options=null) {
            let detail = context.get().detail;
            detail.extensionMsg = "Submitting...";
            ractive.set("detail",detail);
            let lock;
            lock = false;
            if(options !== null && options.option ==='lock')
                lock = true;
            $.post(extensionURL, {
                _token: "{{ csrf_token() }}",
                sid: detail.student.id,
                lock: lock,
                lock_message: detail.student.extension.lock_message ?? null,
                allow: allow,
                expires_at: detail.student.extension.expires_at,
            })
                .done(function(response) {
                    console.log(response);
                    if(response.status !== 'success') {
                        detail.extensionMsg = "Error";
                        ractive.set("detail",detail);
                        return;
                    }
                    detail.extensionMsg = null;
                    detail.student.extension = response.ext;
                    let students = ractive.get("students");
                    console.log(detail.student.id, students.findIndex(x=>x.id === detail.student.id));
                    students[students.findIndex(x=>x.id === detail.student.id)] = detail.student;
                    let extensions = ractive.get("assignment.extensions");
                    let index = extensions.findIndex(x=>x.id === response.ext.id);
                    console.log("index", index)
                    if(response.extension === false) {//Remove the extension if it's been revoked
                        extensions.splice(index, 1);
                        detail.student.extension = {
                            lock_message: response.ext.lock_message ?? null,
                            lock: false,
                        }
                    }
                    else {
                        if (index > 0)  //Replace the extension if it already exists
                            extensions[index]=response.ext;
                        else  //Add the extension if it's new.
                            extensions.push(response.ext);

                    }
                    let extension_calendar = ractive.get("extension_calendar");
                    extension_calendar.setDate(response.ext.expires_at);
                    console.log("extensions", extensions);
                    ractive.set("detail",detail);
                    ractive.update();
                    tooltip();
                })
                .fail(function(xhr,status,error) {
                    console.log(error);
                    detail.extensionMsg = "Error";
                    ractive.set("detail",detail);
                });
        });

        ractive.on("share", function(context) {
            let variable = context.get();
            console.log(variable);
            $.post("share/share_variable", {
                _token: "{{ csrf_token() }}",
                variable_id: variable.id,
            })
                .done(function (response) {
                    console.log(response);
                    variable.shared = response.variable.shared;
                    ractive.update();
                })
                .fail(function(xhr,status,error) {
                    console.log(error);
                });

        });

        ractive.on("sort", function (context, mode, column, qInd = 0, vInd = 0) {
            let array;
            if(mode === 'quiz')
                array = ractive.get("assignment.quiz_jobs");
            else if(mode === 'written')
                array = ractive.get("written."+qInd+".entries");
            else
                array = ractive.get("students");

            console.log(array);

            //If the table is already sorted by this column, reverse it.
            if(column === ractive.get("sortColumn")) {
                array.reverse();
                if(mode === 'quiz')
                    this.set("assignment.quiz_jobs", array);
                else if(mode === 'written')
                    this.set("written."+qInd+".entries", array);
                else
                    this.set("students", array);
                tooltip();
                return;
            }

            array.sort(function(a,b) {
                if(mode === 'quiz' && ['first','last'].includes(column)) {
                    a = a.user;
                    b = b.user;
                }
                else if(mode === 'quiz' && ['seat'].includes(column)) {
                    a = data.getStudent(a.user.id);
                    b = data.getStudent(b.user.id);
                }
                else if(mode === 'written' && ['first','last','seat'].includes(column)) {
                    a = a.student;
                    b = b.student;
                }
                if(column === 'first') {
                    a = a.firstname.toLowerCase();
                    b = b.firstname.toLowerCase();
                }
                else if(column === 'last') {
                    a = a.lastname.toLowerCase();
                    b = b.lastname.toLowerCase();
                }
                else if(column === 'seat') {
                    if(a.pivot.seat == null)
                        a = "";
                    else
                        a = a.pivot.seat.toLowerCase();
                    if(b.pivot.seat == null)
                        b = "";
                    else
                        b = b.pivot.seat.toLowerCase();
                }
                else if(column === 'total') {
                    a = a.total.earned;
                    b = b.total.earned;
                }
                else {
                    if(mode === 'points') {
                        if(a.questions[column].result == null)
                            a = -1;
                        else
                            a = a.questions[column].result.earned;
                        if(b.questions[column].result == null)
                            b = -1;
                        else
                            b = b.questions[column].result.earned;
                    }
                    else if(mode === 'variables' || mode === 'simple') {
                        if(a.questions[qInd].answers[vInd] == null)
                            a = "";
                        else {
                            a = a.questions[qInd].answers[vInd].submission;
                            if(isNaN(a))
                                a = a.toLowerCase();
                            else
                                a = Number(a);
                        }
                        if(b.questions[qInd].answers[vInd] == null)
                            b="";
                        else {
                            b = b.questions[qInd].answers[vInd].submission;
                            if(isNaN(b))
                                b = b.toLowerCase();
                            else
                                b = Number(b);
                        }

                    }
                    else if(mode === 'quiz') {
                        a = a[column];
                        b = b[column];
                    }
                    else if(mode === 'written') {
                        if(column === 'created') {
                            a = a.answer.created_at;
                            b = b.answer.created_at;
                        }
                        else if(column === 'updated') {
                            a = a.answer.updated_at;
                            b = b.answer.updated_at;
                        }
                        else if(column === 'score') {
                            a = a.result.earned;
                            b = b.result.earned;
                        }
                        else if(column === 'similarity') {
                            a = a.similarity[0].similarity;
                            b = b.similarity[0].similarity;
                        }
                    }
                }
                return a < b ? -1 : 1;
            });

            if(mode == 'quiz')
                this.set("assignment.quiz_jobs",array);
            else if(mode === 'written')
                this.set("written."+qInd+".entries", array);
            else
                this.set("students",array);
            this.set("sortColumn", column);
            tooltip();
        });

        ractive.on("csv", function(context,type) {
            let questions = ractive.get("assignment.questions");
            let students = ractive.get("students");
            let show_incorrect = ractive.get("show_incorrect");

            let csv = [];
            let headers = ["First Name","Last Name","Seat","Email"];
            if(type==='points')
                headers.push("Total ("+ractive.get("assignment_total")+")");
            for (let i=0; i < questions.length; i++) {
                if(gradedCheck(i)) {
                    let line = questions[i].name.trim();
                    if (type === 'points')
                        line += " (" + questions[i].max_points + ")";
                    if (['points', 'attempts'].includes(type) || (type === 'written' && questions[i].type === 2))
                        headers.push(line);
                    if (type === 'values') {
                        if (questions[i].type === 1) {
                            for (let k = 0; k < questions[i].variables.length; k++) {
                                headers.push(questions[i].variables[k].title + " (" + questions[i].variables[k].name + ")");
                            }
                        }
                        if (ractive.get("show_simple") === true && [3, 5, 7].includes(questions[i].type))
                            headers.push(questions[i].name);
                    }
                }
            }
            if(type === 'quiz') {
                students = ractive.get("assignment.quiz_jobs");
                var student_list = ractive.get("students");
                headers.push('Time Started','Elapsed Time');
            }
            csv.push(headers);

            for (let i = 0; i < students.length; i++) {
                var row;
                if (['points', 'attempts', 'written', 'values'].includes(type))
                    row = [students[i].firstname, students[i].lastname, students[i].pivot.seat, students[i].email];
                else if (type === 'quiz') {
                    let student = student_list.find(x => x.id === students[i].user.id);
                    if(student === undefined)
                        row = [students[i].user.firstname, students[i].user.lastname, "Error", students[i].user.email];
                    else
                        row = [student.firstname, student.lastname, student.pivot.seat, student.email];
                }
                if (type === 'points')
                    row.push(students[i].total.earned);
                if (['points', 'attempts', 'written', 'values'].includes(type)) {
                    for (let j = 0; j < students[i].questions.length; j++) {
                        if (gradedCheck(j)) {
                            if (type === 'points') {
                                if (students[i].questions[j].result == null)
                                    row.push("0");
                                else
                                    row.push(students[i].questions[j].result.earned);
                            }
                            else if (type === 'attempts') {
                                if (students[i].questions[j].result == null)
                                    row.push("0");
                                else
                                    row.push(students[i].questions[j].result.attempts);
                            }
                            else if (type === 'written') {
                                if (questions[j].type === 2) {
                                    if (students[i].questions[j].result == null || students[i].questions[j].answers === undefined || students[i].questions[j].answers[0] === undefined)
                                        row.push("");
                                    else
                                        row.push(students[i].questions[j].answers[0].submission);
                                }
                            }
                            else if (type === 'values') {
                                if (questions[j].type === 1) {
                                    for (let k = 0; k < questions[j].variables.length; k++) {
                                        if (students[i].questions[j].answers === undefined || students[i].questions[j].answers[questions[j].variables[k].id] === undefined)
                                            row.push("");
                                        else if (show_incorrect || students[i].questions[j].result && students[i].questions[j].result.earned == questions[j].max_points)
                                            row.push(students[i].questions[j].answers[questions[j].variables[k].id].submission);
                                        else
                                            row.push("");
                                    }
                                }
                                else if ([3, 5, 7].includes(questions[j].type)) {
                                    if (students[i].questions[j].answers === undefined || students[i].questions[j].answers[0] === undefined)
                                        row.push("");
                                    else if (show_incorrect || students[i].questions[j].result && students[i].questions[j].result.earned == questions[j].max_points)
                                        row.push(students[i].questions[j].answers[0].submission);
                                    else
                                        row.push("");
                                }
                            }
                        }
                    }
                }
                else if (type === 'quiz') {
                    row.push(students[i].actual_start, students[i].elapsed_time);
                }
                csv.push(row);
            }

            for (var i = 0; i < csv.length; i++) {
                csv[i] = csv[i].join("\t");
            }
            csv = csv.join("\n");
            ractive.set("csv_text", csv);
        });

        ractive.on("csv_download", function (event) {
            let csv_text = ractive.get("csv_text");
            let a = document.createElement("a");
            a.href        = 'data:attachment/csv,' +  encodeURIComponent(csv_text);
            a.target      = '_blank';
            a.download    = ractive.get("assignment").name + "_results.tsv";

            document.body.appendChild(a);
            a.click();
        });

        var img = new Image();
        @if($instructor == 1)
            img.src = "{{ url('instructor/course/'.$course->id.'/classroomImage?'.time()) }}";  //Time is added to the filename to prevent browser from cacheing old image after upload.
        @else
            img.src = "{{ url('course/'.$course->id.'/LA/classroomImage?'.time()) }}";  //Time is added to the filename to prevent browser from cacheing old image after upload.
        @endif

        ractive.on("select_classroom", function(context) {
            ractive.set("selected_view","classroom");

            var canvas = document.getElementById('classCanvas');

            ractive.set("canvas", canvas);

            //TODO: this has a problem if image isn't already loaded.  Used to be inside
            //an img.onload() function, but this is problematic when coupled with ractive
            //and switching over to the classroom view.

            var dpr = 2;  //Make the canvas too big at first to improve image resolution.
            var rect = canvas.getBoundingClientRect();

            canvas.width = rect.width * dpr;
            canvas.height = canvas.width;//rect.height * dpr;
            var ctx = canvas.getContext('2d');

            ctx.drawImage(img, 0, 0,canvas.width,canvas.height);

            //Scale everything back down to fit.
            canvas.style.width=canvas.parentNode.offsetWidth-40+'px';
            if (canvas.parentNode.offsetWidth > window.innerHeight*.75)   //Make sure it all fits on a vertical page
                canvas.style.width = window.innerHeight*.75 + 'px';
            canvas.style.height=canvas.style.width;

            addSeats(canvas);

            window.addEventListener('resize', function(evt) {
                canvas.style.width=canvas.parentNode.offsetWidth-40+'px';
                canvas.style.height=canvas.parentNode.offsetWidth-40+'px';
            });

        });

        ractive.on("select_written", function(context) {
            ractive.set("selected_view","written");
            MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
        });


        ractive.on("select_q", function(context, val) {
            ractive.set('selected_q',val);
            addSeats(ractive.get("canvas"));
        });

        ractive.on("load_values", function(context, val) {
            console.log("lazy loading values");
            let questions = ractive.get("assignment.questions");
            let question = questions[val];
            question.loadingMsg = "Loading values...";
            ractive.update();
            $.post('load_answers',
                {
                    _token: "{{ csrf_token() }}",
                    question_id: questions[val].id,
                })
                .done(function (resp) {
                    console.log("response");
                    console.log(resp);
                    questions[val].answers = resp.answers;
                    question.loaded = true;
                    question.loadingMsg = null;
                    ractive.update();
                    //setup_written_entries(qind, val, resp.answers);
                    put_answers_in_table(val);
                    console.log("Done with setup");
                    MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
                })
                .fail(function(xhr,status,error) {
                        console.log(xhr);
                        question.loadingMsg = "Error loading values: "+xhr.responseJSON.message;
                        ractive.update();
                    }
                );
        });

        ractive.on("load_all_values", function(context) {
           console.log("lazy loading all answers");
           ractive.set("valuesLoadingMsg", "Attempting to load all values...");
            $.post('load_all_answers',
                {
                    _token: "{{ csrf_token() }}",
                })
                .done(function (resp) {
                    console.log("response");
                    console.log(resp);
                    setup_loaded_answers(resp.answers);
                    ractive.set("valuesLoadingMsg", null);
                    ractive.set("allLoaded", true);
                    ractive.update();
                    console.log("Done with setup");
                    MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
                })
                .fail(function(xhr,status,error) {
                        console.log(xhr);
                        ractive.set("valuesLoadingMsg", "Error loading values: "+xhr.responseJSON.message);
                        ractive.update();
                    }
                );
        });

        function load_student_values(student) {
            console.log("lazy loading student values");
            student.loadingMsg = "Loading student answers...";
            ractive.update();
            $.post('load_student_answers',
                {
                    _token: "{{ csrf_token() }}",
                    student_id: student.id,
                })
                .done(function (resp) {
                    console.log("response");
                    console.log(resp);

                    setup_student_answers(student, resp.answers);
                    student.loaded = true;
                    student.loadingMsg = null;
                    ractive.update();
                    console.log("Done with setup");
                    MathJax.Hub.Queue(["Typeset",MathJax.Hub]);

                })
                .fail(function(xhr,status,error) {
                        console.log(xhr);
                        student.loadingMsg = "Error loading student answers: "+xhr.responseJSON.message;
                        ractive.update();
                    }
                );
        }

        ractive.on("select_wq", function(context, val) {
            if(ractive.get("lazy")) {
                let writtenQ = ractive.get("written."+val);
                let questions = ractive.get("assignment.questions");
                qind = questions.findIndex(q => q.id === writtenQ.question.id);
                console.log("lazy loading");
                if(writtenQ.entries.length === 0) { //No entires; lazy load them from the server
                    ractive.set("written."+val+".loading",true);
                    ractive.set("written."+val+".loadingMsg", "Loading written entires. Please wait...");
                    $.post('load_answers',
                        {
                            _token: "{{ csrf_token() }}",
                            question_id: writtenQ.question.id,
                        })
                        .done(function (resp) {
                            console.log(resp);
                            setup_written_entries(qind, val, resp.answers);
                            console.log("Done with setup");
                            ractive.set("written."+val+".loading",false);
                            MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
                        })
                        .fail(function(xhr,status,error) {
                                console.log(xhr);
                                ractive.set("written."+val+".loadingMsg", "Error loading entries: "+error);
                            }
                        );
                }
                else
                    console.log("Entires already loaded. Skipping lazy load.");
            }
            ractive.set("wq",val);
            MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
        });

        function addSeats(canvas) {

            var ctx = canvas.getContext('2d');
            ctx.clearRect(0,0,canvas.width,canvas.height);
            ctx.drawImage(img, 0, 0,canvas.width,canvas.height);
            var assignment = ractive.get("assignment");
            let students = ractive.get("students");
            var seats = ractive.get("seats");
            var selected_q = ractive.get("selected_q");
            ctx.font = "bold 16px Arial";
            ctx.textAlign = 'center';
            var color, score;

            var noLocation = [];

            for(var key in students) {
                let seatIndex = -1;
                if(students[key].pivot.seat != null)
                    seatIndex = seats.findIndex(obj => obj.name.toLowerCase() === students[key].pivot.seat.toLowerCase());
                if(seatIndex == -1)
                    noLocation.push(students[key]);
                else {
                    let seat = seats[seatIndex];
                    try {
                        if(selected_q === 'assignment')
                            score = students[key].total.fraction/100;
                        else
                            score = students[key].questions[selected_q].result.earned/assignment.questions[selected_q].max_points;
                        color = getColorForPercentage(score);
                    }
                    catch(e) {
                        color='black';
                    }
                    color = color.replace(";","");
                    if(color=="")
                        color='red';
                    ctx.fillStyle = color;
                    ctx.fillText(students[key].firstname, seat.x * canvas.width, seat.y * canvas.height);
                }
            }
            ractive.set("noLocation", noLocation);
            console.log(noLocation);
        }

        //https://stackoverflow.com/questions/7128675/from-green-to-red-color-depend-on-percentage/7128796
        var percentColors = [
            { pct: 0.0, color: { r: 0xff, g: 0x00, b: 0 } },
            { pct: 0.5, color: { r: 0xff, g: 0xff, b: 0 } },
            { pct: 1.0, color: { r: 0x00, g: 0xff, b: 0 } } ];

        var getColorForPercentage = function(pct) {
            for (var i = 1; i < percentColors.length - 1; i++) {
                if (pct < percentColors[i].pct) {
                    break;
                }
            }
            var lower = percentColors[i - 1];
            var upper = percentColors[i];
            var range = upper.pct - lower.pct;
            var rangePct = (pct - lower.pct) / range;
            var pctLower = 1 - rangePct;
            var pctUpper = rangePct;
            var color = {
                r: Math.floor(lower.color.r * pctLower + upper.color.r * pctUpper),
                g: Math.floor(lower.color.g * pctLower + upper.color.g * pctUpper),
                b: Math.floor(lower.color.b * pctLower + upper.color.b * pctUpper)
            };
            return 'rgb(' + [color.r, color.g, color.b].join(',') + ')';
            // or output as hex if preferred
        }

        ractive.on('addResponse', function(context) {

            let result;

            if(context.getParent().getParent().get().batch === true)
                result = ractive.get("result");
            else
                result = context.getParent().getParent().get().result;

            if(result.feedback === undefined || result.feedback === null)
                result.feedback = "";

            let response = context.get().response;

            if (result.feedback.length > 0)
                result.feedback += " " + response;
            else
                result.feedback += response;

            if(result.batch === true)
                ractive.set("result", result);

            ractive.update();
        });

        ractive.on("saveResponse", function (context) {
            if(context.get().batch === true)
                ractive.set("result.save_response",context.node.checked);
            else {
                let result = context.get().result;
                result.save_response = context.node.checked;
            }
            ractive.update();
        });

        ractive.on("newResponse", function(context) {
            let question = ractive.get("written."+ractive.get("wq")+".question");
            question.responses.push({id: -1, response: "", order: question.responses.length});
            ractive.update();
        });

        ractive.on("deleteResponse", function(context) {
            console.log(context.get());
        });

        ractive.on("addScore", function(context, value, parent=false) {
            if(context.get().batch === true)
                ractive.set("result.earned",value);
            else if(parent && context.getParent().getParent().get().batch == true)
                ractive.set("result.earned",value);
            else if(parent ) {
                console.log(context.getParent().getParent().get());
                let result = context.getParent().getParent().get().result;
                result.earned = value;
            }
            else {
                let result = context.get().result;
                result.earned = value;
            }
            ractive.update();
        });

        ractive.on("applyBatch", function(context, type) {
            let entries = ractive.get("written."+ractive.get("wq")+".entries");
            let batchResult = ractive.get("result");
            for(let i=0; i<entries.length; i++) {
                if(entries[i].selected === true) {
                    if(['score','all'].includes(type))
                        entries[i].result.earned = batchResult.earned;
                    if(['feedback','all'].includes(type))
                        entries[i].result.feedback = batchResult.feedback;
                }
            }
            ractive.update();
        });

        ractive.on("selectAll", function(context, status, boolVal) {
            let entries = ractive.get("written."+ractive.get("wq")+".entries");
            for(let i=0; i<entries.length; i++) {
                if(entries[i].result !== undefined && entries[i].result.status === status) {
                    entries[i].selected = boolVal;
                }
            }
            ractive.update();
        });

        ractive.on("save_responses", function(context) {
            let question = ractive.get("written."+ractive.get("wq")+".question");
            ractive.set("saveResponseMsg", "Saving...");
            ractive.set("saveResponseError",null);
            $.post(responsesURL,
                {
                    _token: "{{ csrf_token() }}",
                    question: JSON.stringify(question)
                })
                .done(function (resp) {
                    console.log(resp);
                    question.responses = resp.responses;
                    ractive.set("saveResponseMsg", "Save");
                    ractive.update();
                    $("#response_modal").modal('hide');
                })
                .fail(function(xhr,status,error) {
                        console.log(xhr);
                        ractive.set("saveResponseMsg", "Error Saving");
                        ractive.set("saveResponseError",xhr.responseJSON.message);
                    }
                );
        });

        ractive.on("save_options", function(context) {
            let question = ractive.get("written."+ractive.get("wq")+".question");
            ractive.set("saveOptionsMsg", "Saving...");
            ractive.set("saveOptionsError",null);
            $.post(optionsURL,
                {
                    _token: "{{ csrf_token() }}",
                    question: JSON.stringify(question)
                })
                .done(function (resp) {
                    console.log(resp);
                    ractive.set("saveOptionsMsg", "Save Options");
                    ractive.update();
                })
                .fail(function(xhr,status,error) {
                        console.log(xhr);
                        ractive.set("saveOptionsMsg", "Error Saving");
                        ractive.set("saveOptionsError",xhr.responseJSON.message);
                    }
                );
        });

        ractive.on("release_deferred", function(context) {
            let results = [];
            let entries = ractive.get("written."+ractive.get("wq")+".entries");
            for(let i=0; i<entries.length; i++) {
                if(entries[i].result !== undefined && entries[i].result.status === 3) {
                    entries[i].result.set_status = 1;
                    results.push(entries[i].result);
                }
            }
            console.log(results);
            submitWritten(results);
        });

        ractive.on("detailRetry", function(context, index) {
            let result = ractive.get("detail.student.questions."+index+".result");
            console.log(result);
            result.set_status = 2;
            submitWritten([result]);
        });

        ractive.on("detailRefreshValues", function(context, index) {

            let question = context.get();
            let detail = ractive.get("detail");
            console.log("attempting refresh");
            if(detail.student.refreshMsgs == undefined)
                detail.student.refreshMsgs = [];
            detail.student.refreshMsgs[index] = "Updating...";
            ractive.update();

            $.post('../newValuesForUser/'+question.id+'/'+detail.student.id,
                {
                    _token: "{{ csrf_token() }}",
                })
                .done(function (resp) {
                    console.log("response");
                    console.log(resp);
                    detail.student.refreshMsgs[index] = resp.msg;
                    ractive.update();
                })
                .fail(function(xhr,status,error) {
                        console.log(xhr);
                        detail.student.refreshMsgs[index] = "Error refreshing values: "+xhr.responseJSON.message;
                        ractive.update();
                    }
                );
        });

        ractive.on('apply_img_width', function(context, w) {
            wq = this.get("written");
            $("img").width(wq[w].img_width);
        });

        ractive.on('apply_response_box_height', function(context, w) {
           wq = this.get("written");
           let boxes = document.getElementsByClassName("response-box");
           for(let i=0; i<boxes.length; i++)
               boxes[i].style.height=wq[w].response_box_height + 'px';
        });

        ractive.on('written_value', function(context, w, q, v) {
            this.toggle('assignment.questions.'+q+'.variables.'+v+'.written_display.'+w);
            MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
        });

        ractive.on('written_other_display', function(context, w, q) {
            this.toggle('assignment.questions.'+q+'.written_display.'+w);
            MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
        });

        ractive.on('submitSingle', function(context, value) {
            let result = context.get().result;
            let options = ractive.get("written."+ractive.get("wq")+".question.options");
            if(value === 1 && options != null && options.releaseMode === 'deferred')
                value = 3;  //Set deferred status
            result.set_status = value;
            let results = [result];
            submitWritten(results);
        });

        ractive.on("submitBatch", function(context, value) {
            //let result = context.get().result;
            let results = [];
            let options = ractive.get("written."+ractive.get("wq")+".question.options");
            if(value === 1 && options != null && options.releaseMode === 'deferred')
                value = 3;  //Set deferred status
            //result.set_status = value;
            let entries = ractive.get("written."+ractive.get("wq")+".entries");
            for(let i=0; i<entries.length; i++) {
                if(entries[i].selected === true) {
                    entries[i].result.set_status = value;
                    results.push(entries[i].result);
                }
            }
            if(ractive.get("result.save_response")===true && results.length > 0)
                results[0].save_response = true;
            ractive.set("result.save_response", false);
            console.log(results);
            submitWritten(results);
        });

        function submitWritten(results) {
            if(results.length === 0)
                return;
            $.post(postURL, {_token: "{{ csrf_token() }}", results: JSON.stringify(results)},
                function (resp) {
                    console.log(resp);
                    let written = ractive.get("written");
                    let wq = ractive.get("wq");
                    let questions = ractive.get("assignment.questions");
                    let students = ractive.get("students");
                    let qindex = questions.findIndex(obj => obj.id === written[wq].question.id);
                    for(let i=0; i<resp.results.length; i++) {
                        //Set the results to what are now saved in the database
                        let index = written[wq].entries.findIndex(obj => obj.result.id === resp.results[i].id);
                        written[wq].entries[index].result = resp.results[i];
                        written[wq].entries[index].selected = false;
                        //Update the student list (used in the other results views)
                        let sindex = students.findIndex(obj => obj.id === resp.results[i].user_id);
                        students[sindex].questions[qindex].result = resp.results[i];
                        updateTotal(students[sindex]);
                    }
                    //Update the responses in case one was saved
                    let question = ractive.get("written."+ractive.get("wq")+".question");
                    question.responses = resp.responses;

                    ractive.update();
                    MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
                });
        }

        //Compute simple cosine similarities for all submissions.
        ractive.on("compute_similarities", function(context) {
            let wq = ractive.get("wq");
            let entries = ractive.get("written."+wq+".entries");
            entries.forEach(e1 => {
                e1.similarity = [];
                txt1 = strip_tags(e1.answer.submission);
                entries.forEach(e2 => {
                    if(e1.answer.id === e2.answer.id)  //Don't compute against self
                        return;
                    txt2 = strip_tags(e2.answer.submission);
                    let similarity = checkSimilarity(txt1,txt2);
                    e1.similarity.push({
                        id: e2.answer.id,
                        user_id: e2.answer.user_id,
                        firstname: e2.student.firstname,
                        lastname: e2.student.lastname,
                        submission: txt2,//e2.answer.submission,
                        similarity: similarity,
                    });
                });
                e1.stripped_submission = txt1;
                e1.similarity.sort((a,b) => (a.similarity < b.similarity ? 1 : -1))
            });
            ractive.set("written."+wq+".similarity_computed",true);
        });

        function compute_time_diff_matrix() {
            let stud = ractive.get("students");
            let mat = [];
            stud.forEach((s1,si) => {
                mat[si] = [];
                stud.forEach((s2,sj) => {
                    mat[si][sj] = [];
                    s1.questions.forEach((q,qi) => {
                        if(q.result !== undefined && s2.questions[qi].result !== undefined) {
                            let t1 = moment(q.result.created_at, "YYYY-MM-DD HH:mm:ss");
                            let t2 = moment(s2.questions[qi].result.created_at, "YYYY-MM-DD HH:mm:ss");
                            mat[si][sj][qi] = t1.diff(t2);
                        }
                    });
                });
            });
            return mat;
        }

        function compute_time_sum_squares(mat) {
            mat2 = [];
            for(let i = 0; i< mat.length; i++) {
                mat2[i] = [];
                for(let j = 0; j < i; j++) {
                    mat2[i][j] = 0;
                    mat[i][j].forEach(d => {
                        mat2[i][j] += Math.pow(d,2);
                    })
                }
            }
            return mat2;
        }

        function list_time_sums(mat) {
            let stud = ractive.get("students");
            let list = [];
            let k = 0;
            mat.forEach((m,i) => {
               m.forEach((m2,j) => {
                   list[k] = {name1: stud[i].lastname, name2: stud[j].lastname, val: m2};
                   k++;
               });
            });
            list.sort(function(a,b) {
                a = a.val;
                b = b.val;
                return a < b ? -1 : 1;
            });
            return list;
        }

        function strip_tags(str) {
            let div = document.createElement("div");
            div.innerHTML = str;
            return div.textContent || div.innerText || "";
        }

        function init_quiz_calendars() {
            init_flatpickr('quiz_start_ind', 'start_calendar_ind');
            init_flatpickr('quiz_end_ind', 'end_calendar_ind');
            init_flatpickr('quiz_start_batch', 'start_calendar_batch');
            init_flatpickr('quiz_end_batch', 'end_calendar_batch');
            init_flatpickr('quiz_start_all', 'start_calendar_all');
            init_flatpickr('quiz_end_all', 'end_calendar_all');
            seed_quiz_settings_calendar("start_calendar_all","allowed_start");
            seed_quiz_settings_calendar("end_calendar_all","allowed_end");
        }

        function init_flatpickr(dataId, storeName) {
            let cal = flatpickr("input[data-id='"+dataId+"'",
                {
                    enableTime: true,
                    dateFormat: "Y-m-d H:i",
                    altInput: true,
                    altInputClass: "inline-block",
                    altFormat: "F j, Y H:i",
                }
            );

            ractive.set(storeName, cal);
        }

        function seed_quiz_settings_calendar(storeName,option) {
            let assignment = ractive.get("assignment");
            let cal = ractive.get(storeName);
            cal.setDate(assignment.options.quiz[option]);
            cal.config.onChange.push(function(selectedDates, dateStr, instance) {
                assignment.options.quiz[option] = dateStr;
                ractive.update();
            });
        }

        function seed_quiz_detail_calendars(job, batch = false) {
            let loc = batch ? "batch" : "ind";

            let start_calendar_ind = ractive.get("start_calendar_" + loc);
            let end_calendar_ind = ractive.get("end_calendar_" + loc);
            let quiz_detail = ractive.get("quiz_detail");
            start_calendar_ind.setDate(job.allowed_start);
            start_calendar_ind.config.onChange.push(function(selectedDates, dateStr, instance) {
                quiz_detail.allowed_start = dateStr;
                ractive.update();
            });
            end_calendar_ind.setDate(job.allowed_end);
            end_calendar_ind.config.onChange.push(function(selectedDates, dateStr, instance) {
                quiz_detail.allowed_end = dateStr;
                ractive.update();
            });
        }

        function seed_detail_calendars(detail) {
            init_flatpickr('extension_date', 'extension_calendar');
            let extension_calendar = ractive.get("extension_calendar");
            if(detail.student.extension === undefined)
                detail.student.extension = {};
            extension_calendar.setDate(detail.student.extension.expires_at);
            extension_calendar.config.onChange.push(function(selectedDates, dateStr, instance) {
                detail.student.extension.expires_at = dateStr;
                ractive.set("detail", detail);
                ractive.update();
            });
        }

        if(data.assignment.type === 2)
            init_quiz_calendars();

        ractive.on("addQuizPage", function(event) {
            ractive.push("assignment.options.quiz.pages",{});
        });
        ractive.on("dropQuizPage", function(event, index) {
            ractive.splice("assignment.options.quiz.pages",index,1);
        });
        ractive.on("addQuizGroup", function(event,pindex) {
            ractive.push("assignment.options.quiz.pages."+pindex+".groups",{});
        });
        ractive.on("dropQuizGroup", function(event, pindex, gindex) {
            ractive.splice("assignment.options.quiz.pages."+pindex+".groups",gindex,1);
        });

        ractive.on("show_quiz_detail", function (event) {
            ractive.set("quizDetailsMsg","");
            console.log(event);
            let job = ractive.get("assignment.quiz_jobs")[event.node.id];
            let detail = JSON.parse(JSON.stringify(job));  //Copy the job object
            ractive.set("quiz_detail", detail);
            seed_quiz_detail_calendars(detail);
            ractive.update();
            $("#quiz_detail_modal").modal('show');
        });

        ractive.on("show_quiz_batch_detail", function (event) {
            ractive.set("quizDetailsMsg","");

            let jobs = ractive.get("assignment.quiz_jobs");
            jobs = jobs.filter(j => j.selected === true);
            if(jobs.length === 0) {
                console.log("No jobs selected.");
            }
            else {
                job = jobs[0];  //Base off of the first job on the list.
                let detail = JSON.parse(JSON.stringify(job));  //Copy the job object
                console.log(detail);
                ractive.set("quiz_detail", detail);
                seed_quiz_detail_calendars(detail, true);
                ractive.update();
                $("#quiz_batch_detail_modal").modal('show');
            }
        });

        ractive.on("quiz_select_all", function(context) {
            let quiz_jobs = ractive.get("assignment.quiz_jobs");
            quiz_jobs.forEach(q => q.selected = true);
            ractive.update();
        });

        ractive.on("quiz_select_none", function(context) {
            let quiz_jobs = ractive.get("assignment.quiz_jobs");
            quiz_jobs.forEach(q => q.selected = false);
            ractive.update();
        });

        function detailsPost(postObj, postURL) {
            ractive.set("quizDetailsMsg", "Updating...");

            postObj._token = "{{ csrf_token() }}";

            $.post(postURL,
                postObj)
                .done(function (resp) {
                    console.log(resp);
                    if(resp.status==="success") {
                        if(resp.message !== undefined)
                            ractive.set("quizDetailsMsg", resp.message);
                        else
                            ractive.set("quizDetailsMsg", "Updated quiz status.");
                        let quiz_jobs = ractive.get("assignment.quiz_jobs");
                        if(resp.batch !== undefined && resp.batch === true) {
                            resp.quiz_jobs.forEach(j => update_job(quiz_jobs, j));
                        }
                        else {
                            update_job(quiz_jobs, resp.quiz_job);
                            ractive.set("quiz_detail", resp.quiz_job);
                        }
                        ractive.update();
                    }
                    else
                        ractive.set("quizDetailsMsg", "Error: "+resp.message);
                })
                .fail(function(xhr,status,error) {
                        console.log(xhr);
                        ractive.set("quizDetailsMsg","Error Saving: "+xhr.responseJSON.message);
                    }
                );
        }

        function update_job(quiz_jobs, quiz_job) {
            let job_index = quiz_jobs.findIndex(obj => obj.id === quiz_job.id);
            let user = quiz_jobs[job_index].user;
            quiz_jobs[job_index] = quiz_job;
            quiz_jobs[job_index].user = user;
        }

        //Functions for updating individual quiz statuses.
        ractive.on(
            "update_student_quiz_status", function(context, status) {
                let quiz_detail = ractive.get("quiz_detail");
                let postObj = {
                    job: JSON.stringify(quiz_detail),
                    status: status,
                };
                detailsPost(postObj, quizDetailStatus);
            });

        ractive.on("update_student_quiz_page_status", function(context, pageIndex, status) {
            let quiz_detail = ractive.get("quiz_detail");
            let postObj = {
                job: JSON.stringify(quiz_detail),
                page: pageIndex,
                status: status,
            };
            detailsPost(postObj, quizDetailPageStatus);
        });

        ractive.on("update_student_quiz_detail", function(context) {

            let quiz_detail = ractive.get("quiz_detail");
            let postObj = {
                job: JSON.stringify(quiz_detail),
            };
            detailsPost(postObj, quizDetailURL);
        });

        ractive.on("update_batch_quiz_detail", function(context) {
            let jobs = ractive.get("assignment.quiz_jobs");
            jobs = jobs.filter(j => j.selected === true);
            let quiz_detail = ractive.get("quiz_detail");
            let postObj = {
                jobs: JSON.stringify(jobs),
                detail: JSON.stringify(quiz_detail),
            };
            detailsPost(postObj, quizBatchDetailURL);
        });

        ractive.on("toggle_student_review_state", function(context) {

            let quiz_detail = ractive.get("quiz_detail");
            let postObj = {
                job: JSON.stringify(quiz_detail),
            };
            detailsPost(postObj, quizDetailToggleReview);
        });

        ractive.on("update_student_deferred_results_state", function(context, old_status, new_status) {

            let quiz_detail = ractive.get("quiz_detail");
            let postObj = {
                job: JSON.stringify(quiz_detail),
                old_status: old_status,
                new_status: new_status,
            };
            detailsPost(postObj, quizDetailDeferred);
        });


        ractive.on("update_quiz_settings", function(context) {
            let assignment = ractive.get("assignment");
            ractive.set("quizSettingsMsg","");
            $.post(quizSettingsURL,
                {
                    _token: "{{ csrf_token() }}",
                    options: JSON.stringify(assignment.options)
                })
                .done(function (resp) {
                    console.log(resp);
                    if(resp.status=="success") {
                        ractive.set("quizSettingsMsg", "Updated.");
                        assignment.options = resp.options;
                        console.log(assignment);
                        ractive.update();
                    }
                    else
                        ractive.set("quizSettingsMsg", "Error: "+resp.message);
                })
                .fail(function(xhr,status,error) {
                        console.log(xhr);
                        ractive.set("quizSettingsMsg","Error Saving: "+xhr.responseJSON.message);
                    }
                );
        });


        ractive.on('rescore_question', function(context, qindex) {
            let questions = ractive.get("assignment.questions");
            let qid = questions[qindex].id;
            let linked = ractive.get("linked");
            let rescoreURL = linked ? 'rescore_question_including_linked' : "rescore_question";
            ractive.set("rescoreMsg","Rescoring...")
            $.post(rescoreURL,
                {
                    _token: "{{ csrf_token() }}",
                    qid: qid,
                })
                .done(function(resp) {
                        console.log(resp);
                        if(resp.status=="success")
                            ractive.set("rescoreMsg",resp.msg);
                        else
                            ractive.set("rescoreMsg", "Error: "+resp.msg);
                    }
                )
                .fail(function(xhr,status,error) {
                        console.log(xhr);
                        ractive.set("rescoreMsg","Error Saving: "+xhr.responseJSON.message);
                    }
                );
        });

        //Initialize quiz instructions editor
        $('#quiz_instructions').summernote({
            placeholder: 'Assignment description that students will see.',
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
                    summernoteOnImageUpload(files,"#quiz_instructions");
                },
                onChange: function(contents, $editable) {
                    ractive.set("assignment.options.quiz.instructions", contents)
                }
            }
        });

    </script>
@stop
