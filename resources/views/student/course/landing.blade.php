@extends('layouts.student')

@section('JS')
    <script src="{{ asset('js/ractive.min.js') }}"></script>
    <script src='{{ asset('/js/moment.min.js') }}'></script>
@endsection

@section('content')

    <div id="target"></div>

    <script id="template" type="text/ractive">
        @include('student.course.landingPage')
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

        data.course.descriptionDisplay = update_dynamic_links(data.course.description);

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
                    if(a.extension != null && a.extension.lock !== 1) {
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
    </script>


@endsection
