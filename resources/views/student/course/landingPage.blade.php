<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-warning" role="alert">
                    {{ session('error') }}
                </div>
            @endif
            <h2>{{$course->name}}</h2>
            [[#course.description != null]]
            <div class="card mb-2">
                <div class="card-body pb-0">
                    [[& course.descriptionDisplay]]
                </div>
            </div>
            [[/course.description]]
            <div class="card-deck">
                <div class="card">
                    <div class="card-header">Dashboard</div>
                    [[#LA]]
                    <div class="alert alert-success" role="alert">
                        You are a Learning Assistant for this course.  Some LA features are still in development.
                    </div>
                    [[/LA]]
                    <div class="card-body">
                        <div class="row">
                            <div class="d-flex col-md-6 align-items-center justify-content-center">
                                <a href="{{url('course/'.$course->id.'/forum/landing')}}" role="button" class="btn btn-outline-dark btn-sm mb-1">
                                    Forum
                                </a>
                            </div>
                            <div class="col-md-6">
                                <div class="card-text"><span class="badge badge-pill [[forum.unread == 0 ? 'badge-success' : 'badge-danger']]">[[forum.unread]]</span> Unread Topics</div>
                                <div class="card-text"><span class="badge badge-pill [[forum.newResponses == 0 ? 'badge-success' : 'badge-danger']]">[[forum.newResponses]]</span> Unread Responses</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                [[#forum.count>0]]
                                <div class="card-text">Last Topic Posted: <a href="{{url('course/'.$course->id.'/forum/view')}}/[[forum.last.id]]">[[forum.last.title]]</a> ([[forum.last.time]])</div>
                                [[/forum.count]]
                                [[#forum.lastResponse != null]]
                                <div class="card-text">Last Response Posted To: <a href="{{url('course/'.$course->id.'/forum/view')}}/[[forum.lastResponse.id]]">[[forum.lastResponse.title]]</a> ([[forum.lastResponse.time]])</div>
                                [[/forum.lastResponse]]
                            </div>
                        </div>
                        <hr>
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <a class="btn btn-sm btn-outline-dark mr-1" href="#assignments">Assignments</a>
                                <a class="btn btn-sm btn-outline-dark mr-1" href="#files">Files</a>
                                [[#hasGrades]]
                                <a class="btn btn-sm btn-outline-dark mr-1" href="grades">Grades</a>
                                [[/hasGrades]]
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">Tools</div>
                    <div class="card-body">
                        <button type="button" class="btn btn-outline-dark btn-sm mb-1" data-toggle="modal" data-target="#seatModal">
                            Change Seat
                        </button>
                        <button type="button" class="btn btn-outline-dark btn-sm mb-1" data-toggle="modal" data-target="#dropModal">
                            Drop Course
                        </button>
                        [[#if LA && LA_privs.edit]]
                        <a href="{{url('course/'.$course->id.'/LA/assignment/new')}}" class="btn btn-outline-dark btn-sm mb-1">Create Assignment</a>
                        [[/if]]
                        [[#if LA]]
                        <a href="LA/review/landing" role="button" class="btn btn-outline-dark btn-sm mb-1">
                            Peer Review
                        </a>
                        [[/if]]
                    </div>
                </div>
            </div>
            <!-- Course Information -->
            [[#infos.length>0]]
            <div class="card mt-2">
                <div class="card-header">
                    <span class="align-middle d-inline-block">Course Information</span>
                    <div class="dropdown d-inline-block">
                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            [[activeInfo !== undefined ? infos[activeInfo].title : 'Select']]
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            [[#infos:i]]
                            <a class="dropdown-item" href="#/" on-click="['selectActiveInfo',i]">[[title]]</a>
                            [[/infos]]
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    [[& activeInfo != undefined ? infos[activeInfo].text.description : 'Select from the dropdown above for information.']]
                </div>
                [[#infos[activeInfo].info_quizzes:iq]]
                <div class="card-footer bg-transparent border-info">
                    [[[description]]]
                    <div class="list-group">
                        [[#info_quiz_questions:iqq]]
                        <div class="list-group-item">
                            [[[description]]]<br/>
                            [[#choices:c]]
                            <div class="btn mr-1 [[../../answer ? 'disabled' : '']] [[../../info_quiz_answers[0].answer.selected.length>0 && ../../info_quiz_answers[0].answer.selected.includes(c.toString()) === true ? 'btn-dark' : 'btn-outline-dark']]" style="white-space:normal;" [[#!../../../../locked]]on-click="quiz-select"[[/locked]] >[[#../../answer && ../../answer.selected.includes(c.toString())]]<i class="fas fa-check" style="color:green;"></i>&nbsp;[[/answer]][[[description]]] </div>
                        [[/choices]]
                        [[#answer]]<br/>[[info_quiz_answers[0].earned == undefined ? '0' : info_quiz_answers[0].earned]]/[[points]] points. Answer(s) marked with <i class="fas fa-check" style="color:green;"></i>.[[/answer]]
                    </div>
                    [[/info_quiz_questions]]
                </div>
                <div class="col text-center">
                    [[#closed]][[closedFormatted]]<br/>[[/closed]]
                    [[#!locked]]<div class="btn btn-sm btn-info mt-1" on-click="quiz-submit">Submit Answers</div>
                    <br/>[[saving]][[/locked]]
                </div>
            </div>
            [[/infos[activeInfo].info_quizzes]]
            [[/infos]]
            <!-- End Course Information -->

            <div class="card-deck mt-3">
                <div class="card" id="assignments">
                    <div class="card-header" >Assignments</div>
                    <div class="card-body">
                        <ul class="list-group flex-column">
                            [[#assignments:a]]
                            <li class="list-group-item px-0 py-0 mb-1">
                                <a href="{{url('course/'.$course->id.'/assignment/[[id]]/view')}}" class="list-group-item py-1 [[#disabled || closed]]list-group-item-secondary[[/disabled]]">
                                    [[name]] [[#disabled]]<em>(disabled)</em>[[/disabled]] [[#closes_soon]]<span class="badge badge-danger">Closes Soon</span>[[/closes_soon]]
                                    <div>[[#closes_at !== null]] <em>[[closed ? 'Closed' : 'Closes' ]] [[&closes_at]]</em>[[/closes_at]]</div>
                                </a>
                                [[#../../LA]]
                                <div class="btn-group">
                                    [[#../../LA]]
                                    <a href="{{url('course/'.$course->id.'/LA/assignment/[[id]]/results/main')}}" class="btn btn-info btn-sm">Results</a>
                                    [[/../../LA]]

                                    [[#if LA && LA_privs.edit]]
                                    <a href="{{url('course/'.$course->id.'/LA/assignment/[[id]]/edit')}}" class="btn btn-secondary btn-sm">Edit</a>
                                    [[/if]]

                                </div>
                                [[/../../LA]]
                                [[#progress.display && ~/course.progress_display]]
                                <div class="progress" data-toggle="tooltip" title="You have submitted [[progress.results]] of [[progress.questions]] questions with [[progress.earnedPercent]]% of the assignment marked successful for the assignment [[name]]." aria-hidden>
                                    [[#progress.difference < 0]]
                                    <div class="progress-bar bg-success" role="progressbar" style="width: [[progress.earnedPercent]]%;" aria-valuenow="[[progress.earnedPercent]]" aria-valuemin="0" aria-valuemax="100"></div>
                                    <div class="progress-bar" role="progressbar" style="width: [[Math.abs(progress.difference)]]%;" aria-valuenow="[[progress.submitPercent]]" aria-valuemin="0" aria-valuemax="100"></div>
                                    [[else]]
                                    <div class="progress-bar bg-success" role="progressbar" style="width: [[progress.earnedPercent]]%;" aria-valuenow="[[progress.earnedPercent]]" aria-valuemin="0" aria-valuemax="100"></div>
                                    [[/progress.difference]]
                                </div>
                                <div class="sr-only">You have submitted [[progress.results]] of [[progress.questions]] questions with [[progress.earnedPercent]]% of the assignment marked successful for the assignment [[name]]..</div>
                                [[/progress.display]]
                            </li>
                            [[/assignments]]
                        </ul>
                    </div>
                </div>
                [[#if LA && LA_privs.edit]]
                <div class="card border-danger">
                    <div class="card-header">Inactive Assignments</div>
                    <div class="card-body">
                        <ul class="list-group flex-column">
                            [[#inactive:a]]
                            <a href="{{url('course/'.$course->id.'/assignment/[[id]]/view')}}" class="list-group-item d-flex justify-content-between align-items-center [[#disabled]]list-group-item-secondary[[/disabled]]">
                                [[name]] [[#disabled]]<em>(disabled)</em>[[/disabled]]
                                <div class="btn-group">
                                    <a href="{{url('course/'.$course->id.'/LA/assignment/[[id]]/results/main')}}" class="btn btn-info btn-sm">Results</a>
                                    <a href="{{url('course/'.$course->id.'/LA/assignment/[[id]]/edit')}}" class="btn btn-secondary btn-sm">Edit</a>
                                </div>
                            </a>
                            [[/inactive]]
                        </ul>
                    </div>
                </div>
                [[/if]]
                [[#if reviews]]
                <div class="card">
                    <div class="card-header">Submission Assignments</div>
                    <div class="card-body">
                        <ul class="list-group flex-column">
                            [[#reviews:r]]
                            <a href="{{url('course/'.$course->id.'/review/[[id]]/view')}}" class="list-group-item d-flex justify-content-between align-items-center">
                                [[name]] [[[action != "" ? '<span class="badge badge-danger">'+action+'</span>' : '']]]
                            </a>
                            [[/reviews]]
                        </ul>
                    </div>
                </div>
                [[/if]]

            </div>
            <div class="card-deck mt-2">
                [[#folders.length > 0]]
                <div class="card" id="files">
                    <div class="card-header" >Files</div>
                    <div class="card-body">
                        [[#folders:f]]
                        <h4>[[name]]</h4>
                        [[#course_files]]
                        [[#extension == "pdf"]]
                        <i class="far fa-file-pdf" style="color:red"></i>
                        [[elseif extension == "docx" || extension == "doc"]]
                        <i class="far fa-file-word" style="color:blue"></i>
                        [[elseif extension == "xlsx" || extension == "xls"]]
                        <i class="far fa-file-excel" style="color:green"></i>
                        [[elseif extension == "pptx" || extension == "ppt"]]
                        <i class="far fa-file-powerpoint" style="color:orange"></i>
                        [[elseif ["jpeg","jpg","png","tiff","tif","gif"].includes(extension)]]
                        <i class="far fa-file-image" style="color:purple"></i>
                        [[else]]
                        <i class="far fa-file"></i>
                        [[/extension]]
                        <a href="{{url('course/'.$course->id.'/files/download/[[id]]')}}" download>[[name]]</a><br/>
                        [[/course_files]]
                        <hr>
                        [[/folders]]
                    </div>
                </div>
                [[/folders]]
            </div>

        </div>
    </div>
</div>

@include('student.course.landingModals')
