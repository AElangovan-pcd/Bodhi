<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {!! session('status') !!}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-warning" role="alert">
                    {!! session('error') !!}
                </div>
            @endif
            <h2>{{$course->name}}</h2>
            [[#course.descriptionDisplay != null]]
            <div class="card mb-2">
                <div class="card-body pb-0">
                    [[& course.descriptionDisplay]]
                </div>
            </div>
            [[/course.description]]
            <div class="card-deck">
                <div class="card">
                    <div class="card-header">Dashboard</div>
                    <div class="card-body">
                        [[#if course.parent_course_id != null || course.linked_courses.length >0]]
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <i class="fas fa-link" aria-hidden=""></i>
                                    [[#course.parent_course_id != null]]
                                    Linked course. This course inherits some content from [[course.linked_parent_course.name]].
                                    [[else]]
                                    Linked course.  This course has [[course.linked_courses.length]] child courses that will inherit some content from this course.
                                    [[/course.parent_course_id]]
                                </div>
                            </div>
                        </div>
                        [[/if]]
                        [[#course.link_requests_incoming.length > 0]]
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    There are requests to link to this course.
                                    <button type="button" class="btn btn-light btn-sm mb-1" data-toggle="modal" data-target="#linkingModal">
                                        Review
                                    </button>
                                </div>
                            </div>
                        </div>
                        [[/course.link_requests_incoming.length]]
                        <div class="row">
                            <div class="d-flex col-md-6 align-items-center justify-content-center">
                                <a href="{{url('instructor/course/'.$course->id.'/forum/landing')}}" role="button" class="btn btn-outline-dark btn-sm mb-1">
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
                                <div class="card-text">Last Topic Posted: <a href="{{url('instructor/course/'.$course->id.'/forum/view')}}/[[forum.last.id]]">[[forum.last.title]]</a> ([[forum.last.time]])</div>
                                [[/forum.count]]
                                [[#forum.lastResponse != null]]
                                <div class="card-text">Last Response Posted To: <a href="{{url('instructor/course/'.$course->id.'/forum/view')}}/[[forum.lastResponse.id]]">[[forum.lastResponse.title]]</a> ([[forum.lastResponse.time]])</div>
                                [[/forum.lastResponse]]
                            </div>
                        </div>
                        <hr>
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <a class="btn btn-sm btn-outline-dark mr-1" href="#assignments">Assignments</a>
                                <a class="btn btn-sm btn-outline-dark mr-1" href="#files">Files</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">Course Tools</div>
                    <div class="card-body">
                        <a href="{{url('instructor/course/'.$course->id.'/assignment/new')}}" role="button" class="btn btn-light btn-sm mb-1">
                            Create Assignment
                        </a>
                        <button type="button" class="btn btn-light btn-sm mb-1" data-toggle="modal" data-target="#uploadModal">
                            Upload Assignment
                        </button>
                        <a href="{{url('instructor/course/'.$course->id.'/polls/landing')}}" role="button" class="btn btn-light btn-sm mb-1">
                            Polls
                        </a>
                        <a href="{{url('instructor/course/'.$course->id.'/review/landing')}}" role="button" class="btn btn-light btn-sm mb-1">
                            Peer Review
                        </a>
                        <a href="{{url('instructor/course/'.$course->id.'/info/landing')}}" role="button" class="btn btn-light btn-sm mb-1">
                            Information
                        </a>
                        <a href="{{url('instructor/course/'.$course->id.'/files/landing')}}" role="button" class="btn btn-light btn-sm mb-1">
                            Files
                        </a>
                        <a href="{{url('instructor/course/'.$course->id.'/totals')}}" role="button" class="btn btn-light btn-sm mb-1">
                            Course Totals
                        </a>
                        <a href="{{url('instructor/course/'.$course->id.'/grades/landing')}}" role="button" class="btn btn-light btn-sm mb-1">
                            Grades
                        </a>
                        <hr>
                        <a href="{{url('instructor/course/'.$course->id.'/manageAssistants')}}" role="button" class="btn btn-light btn-sm mb-1">
                            Manage Learning Assistants
                        </a>
                        <button type="button" class="btn btn-light btn-sm mb-1" data-toggle="modal" data-target="#detailsModal" on-click="@.set('update_details_msg',null)">
                            Edit Course Details
                        </button>
                        <a href="{{url('instructor/course/'.$course->id.'/classroomLayout')}}" role="button" class="btn btn-light btn-sm mb-1">
                            Edit Classroom Layout
                        </a>
                        <a href="{{url('instructor/course/'.$course->id.'/scheduler/landing')}}" role="button" class="btn btn-light btn-sm mb-1">
                            Scheduler
                        </a>
                        <button type="button" class="btn btn-light btn-sm mb-1" data-toggle="modal" data-target="#linkingModal">
                            Course Linking
                        </button>
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
                            <a class="dropdown-item" href="#" on-click="['selectActiveInfo',i]">[[title]]</a>
                            [[/infos]]
                        </div>
                    </div>
                </div>
                [[#~/activeInfo == undefined]]<div class="card-body">Select from the dropdown above for information.</div>[[/~/activeInfo]]
                [[#infos:i]]
                <div class="card-body [[~/activeInfo === i ? '' : 'd-none']]">
                    [[[text.description]]]
                </div>
                [[/infos]]
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
                    <a href="info/results/[[id]]" class="btn btn-sm btn-outline-success mt-1">Results</a><br/>
                    [[#!locked]]<div class="btn btn-sm btn-info mt-1" on-click="quiz-submit">Submit Answers</div>
                    <br/>[[saving]][[/locked]]
                </div>
            </div>
            [[/infos[activeInfo].info_quizzes]]
            [[/infos]]
            <!-- End Course Information -->

            <div class="card-deck mt-3">
                <div class="card border-success" id="assignments">
                    <div class="card-header">Assignments</div>
                    <div class="card-body">
                        <ul class="list-group flex-column" id="assignment_list">
                            [[#assignments:a]]
                            <li data-id="[[id]]" class="py-0 list-group-item [[(selected && ~/selectAssignments) ? 'list-group-item-primary' : !active ? 'list-group-item-danger' : disabled ? 'list-group-item-secondary' : closed ? 'list-group-item-secondary' : '']]">
                                <div class="row">
                                    <div class="col-0 px-0 my-auto">
                                        <span class="input-group-text sorting-handle p-1 mr-1"><i class="fas fa-arrows-alt aria-hidden"></i><span class="sr-only">Move</span></span>
                                    </div>
                                    <div class="col p-0">
                                        <div class="d-flex align-items-center my-auto" [[#~/selectAssignments]]on-click="selectAssignment"[[/~/selectAssignments]]>
                                        [[name]]
                                        [[#disabled]]<em>(disabled)</em>[[/disabled]]
                                        [[#!active]]<em>(inactive)</em>[[/active]]
                                        <div class="ml-auto">
                                            [[#parent_assignment_id != null]]<div class="badge badge-pill badge-dark" data-toggle="tooltip" data-placement="top" title="Linked Assignment. Inherits from [[linked_parent_assignment.course.name]]."><i class="fas fa-link" aria-hidden="true"></i><span class="sr-only">Linked Assignment. Inherits from [[linked_parent_assignment.course.name]].</span></div>[[/parent_assignment_id]]
                                            [[#linked_assignments_count]]<div class="badge badge-dark" data-toggle="tooltip" data-placement="top" title="Linked Assignment. [[linked_assignments_count]] assignments inherit from this assignment."><i class="fas fa-link" aria-hidden="true"></i> <span class="badge badge-pill badge-success">[[linked_assignments_count]]</span><span class="sr-only">Linked Assignment. [[linked_assignments_count]] assignments inherit from this assignment.</span></div>[[/linked_assignments_count]]
                                            [[#ungraded_results_count > 0]]<div class="badge badge-pill badge-info" data-toggle="tooltip" data-placement="top" title="[[ungraded_results_count]] ungraded written responses">[[ungraded_results_count]]</div>[[/ungraded_results_count]]
                                            [[#deferred_results_count > 0]]<div class="badge badge-pill badge-danger" data-toggle="tooltip" data-placement="top" title="There are results with deferred feedback."><i class="fas fa-bed" aria-hidden></i></div>[[/deferred_results_count]]
                                        </div>
                                        [[#active]]
                                        <div class="btn-group ml-1" role="group" aria-label="Assignment Actions">
                                            <div class="btn-group">
                                                <a href="assignment/[[id]]/results/main" role="button" class="btn btn-sm btn-primary" data-toggle="tooltip" data-placement="top" title="Results"><i class="fas fa-clipboard-list" aria-hidden></i><span class="sr-only">Results</span></a>
                                                [[#linked_assignments_count]]
                                                <button type="button" class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <span class="sr-only">Toggle Dropdown</span>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="assignment/[[id]]/results/main">Results for this course's users</a>
                                                    <a class="dropdown-item" href="assignment/[[id]]/results/linked">Results for all linked assignments across courses</a>
                                                </div>
                                                [[/linked_assignments_count]]
                                            </div>
                                            <a href="assignment/[[id]]/edit" role="button" class="btn btn-sm btn-secondary" data-toggle="tooltip" data-placement="top" title="Edit"><i class="far fa-edit" aria-hidden></i><span class="sr-only">Edit</span></a>
                                            <a href="assignment/[[id]]/view" role="button" class="btn btn-sm btn-light" data-toggle="tooltip" data-placement="top" title="View"><i class="far fa-eye" aria-hidden></i><span class="sr-only">View</span></a>
                                            [[#course.linked_courses.length > 0]]
                                            <a href="assignment/[[id]]/pushToLinkedCourses" role="button" class="btn btn-sm btn-info" data-toggle="tooltip" data-placement="top" title="Push to Linked Courses"><i class="fas fa-project-diagram" aria-hidden></i><span class="sr-only">Push to Linked Courses</span></a>
                                            [[/course.linked_courses.length]]
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-dark dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-toggle-second="tooltip" data-placement="top" title="Other Options">
                                                    <span aria-hidden="true">...</span><span class="sr-only">Other Options</span>
                                                </button>
                                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                    <a href="{{url('instructor/course/'.$course->id.'/assignment/[[id]]/export')}}" class="dropdown-item" download><i class="fas fa-file-export" aria-hidden></i> Export</a>
                                                    <a href="{{url('instructor/course/'.$course->id.'/assignment/[[id]]/duplicate')}}" class="dropdown-item"><i class="far fa-copy" aria-hidden></i> Duplicate</a>
                                                    [[#disabled]]
                                                    <a href="{{url('instructor/course/'.$course->id.'/assignment/[[id]]/enable')}}" class="dropdown-item"><i class="fas fa-plus-circle" aria-hidden></i> Enable</a>
                                                    [[#course.linked_courses.length > 0]]
                                                    <a href="{{url('instructor/course/'.$course->id.'/assignment/[[id]]/enable_including_linked')}}" class="dropdown-item"><i class="fas fa-plus-circle" aria-hidden></i> Enable Linked</a>
                                                    [[/course.linked_courses.length]]
                                                    [[else]]
                                                    <a href="{{url('instructor/course/'.$course->id.'/assignment/[[id]]/disable')}}" class="dropdown-item"><i class="fas fa-minus-circle" aria-hidden></i> Disable</a>
                                                    [[#course.linked_courses.length > 0]]
                                                    <a href="{{url('instructor/course/'.$course->id.'/assignment/[[id]]/disable_including_linked')}}" class="dropdown-item"><i class="fas fa-minus-circle" aria-hidden></i> Disable Linked</a>
                                                    [[/course.linked_courses.length]]
                                                    [[/disabled]]
                                                    <a href="{{url('instructor/course/'.$course->id.'/assignment/[[id]]/deactivate')}}" class="dropdown-item"><i class="far fa-times-circle" aria-hidden></i> Deactivate</a>
                                                    [[#course.linked_courses.length > 0]]
                                                    <a href="{{url('instructor/course/'.$course->id.'/assignment/[[id]]/deactivate_including_linked')}}" class="dropdown-item"><i class="far fa-times-circle" aria-hidden></i> Deactivate Linked</a>
                                                    [[/course.linked_courses.length]]
                                                </div>
                                            </div>
                                        </div>
                                        [[else]]
                                        <div class="btn-group ml-1" role="group" aria-label="First group">
                                            <a href="{{url('instructor/course/'.$course->id.'/assignment/[[id]]/edit')}}" role="button" class="btn btn-sm btn-secondary" data-toggle="tooltip" data-placement="top" title="Edit"><i class="far fa-edit" aria-hidden></i><span class="sr-only">Edit</span></a>
                                            <a href="{{url('instructor/course/'.$course->id.'/assignment/[[id]]/view')}}" role="button" class="btn btn-sm btn-light" data-toggle="tooltip" data-placement="top" title="View"><i class="far fa-eye" aria-hidden></i><span class="sr-only">View</span></a>
                                            <div class="btn-group">
                                                <a href="assignment/[[id]]/activate" role="button" class="btn btn-sm btn-success" data-toggle="tooltip" data-placement="top" title="Activate"><i class="fas fa-check" aria-hidden></i><span class="sr-only">Activate</span></a>
                                                [[#linked_assignments_count]]
                                                <button type="button" class="btn btn-sm btn-success dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <span class="sr-only">Toggle Dropdown</span>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="assignment/[[id]]/activate">Activate</a>
                                                    <a class="dropdown-item" href="assignment/[[id]]/activate_including_linked">Activate Linked</a>
                                                </div>
                                                [[/linked_assignments_count]]
                                            </div>
                                            [[#course.linked_courses.length > 0]]
                                            <a href="assignment/[[id]]/pushToLinkedCourses" role="button" class="btn btn-sm btn-info" data-toggle="tooltip" data-placement="top" title="Push to Linked Courses"><i class="fas fa-project-diagram" aria-hidden></i><span class="sr-only">Push to Linked Courses</span></a>
                                            [[/course.linked_courses.length]]
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-dark dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-toggle-second="tooltip" data-placement="top" title="Other Options">
                                                    <span aria-hidden="true">...</span><span class="sr-only">Other Options</span>
                                                </button>
                                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                    <a href="{{url('instructor/course/'.$course->id.'/assignment/[[id]]/results/main')}}" class="dropdown-item"><i class="fas fa-clipboard-list" aria-hidden></i> Results</a>
                                                    <a href="{{url('instructor/course/'.$course->id.'/assignment/[[id]]/export')}}" class="dropdown-item" download><i class="fas fa-file-export" aria-hidden></i> Export</a>
                                                    <a href="{{url('instructor/course/'.$course->id.'/assignment/[[id]]/duplicate')}}" class="dropdown-item"><i class="far fa-copy" aria-hidden></i> Duplicate</a>
                                                    <button class="dropdown-item" data-toggle="modal" data-target="#modal_remove_assignment_[[id]]"><i class="far fa-trash-alt" aria-hidden></i> Delete</button>
                                                </div>
                                            </div>
                                        </div>
                                        [[/active]]
                                    </div>

                                    <div class="row">

                                        <div class="col-md-4">
                                            [[#progress.display && ~/course.progress_display]]
                                            <div class="progress" data-toggle="tooltip" title="You have submitted [[progress.results]] of [[progress.questions]] questions with [[progress.earnedPercent]]% of the assignment marked successful  for the assignment [[name]]." aria-hidden>
                                                [[#progress.difference < 0]]
                                                <div class="progress-bar bg-success" role="progressbar" style="width: [[progress.earnedPercent]]%;" aria-valuenow="[[progress.earnedPercent]]" aria-valuemin="0" aria-valuemax="100"></div>
                                                <div class="progress-bar" role="progressbar" style="width: [[Math.abs(progress.difference)]]%;" aria-valuenow="[[progress.submitPercent]]" aria-valuemin="0" aria-valuemax="100"></div>
                                                [[else]]
                                                <div class="progress-bar bg-success" role="progressbar" style="width: [[progress.earnedPercent]]%;" aria-valuenow="[[progress.earnedPercent]]" aria-valuemin="0" aria-valuemax="100"></div>
                                                [[/progress.difference]]
                                            </div>
                                            <div class="sr-only">You have submitted [[progress.results]] of [[progress.questions]] questions with [[progress.earnedPercent]]% of the assignment marked successful  for the assignment [[name]].</div>
                                            [[/progress.display]]
                                        </div>
                                        <div class="col-md-8 ">
                                            [[#closes_at !== null]] <p class="text-right p-0 m-0">[[#closes_soon]]<span class="badge badge-danger">Closes Soon</span>[[/closes_soon]]<em>[[closed ? 'Closed' : 'Closes' ]] [[closes_at]]</em></p>[[/closes_at]]
                                        </div>
                                    </div>
                                </div>
                            </li>
                            [[/assignments]]
                        </ul>
                    </div>
                    <div class="card-footer">
                        <button type="button" class="btn btn-outline-dark btn-sm mb-1" on-click="updateOrder" data-toggle="tooltip" data-placement="top" title="Drag and drop the assignments, then click this button to save the order.">
                            Save Assignment Order
                        </button>
                        <a href="assignments" class="btn btn-outline-dark btn-sm mb-1">Bulk Edit</a>
                        <button type="button" class="btn [[selectAssignments ? 'btn-primary' : 'btn-outline-dark' ]] btn-sm mb-1" on-click="selectAssignments" data-toggle="tooltip" data-placement="top" title="After turning this on, click on assignments in the list to select them. Then choose an action to the right to apply to all selected assignments.">
                            Select
                        </button>
                        [[#selectAssignments]]

                        <div class="dropdown d-inline-block">
                            <button class="btn btn-sm btn-outline-dark dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-toggle-second="tooltip" data-placement="top" title="Other Options">
                                <span>Apply...</span>
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <a href="#/" class="dropdown-item" on-click="['applyToSelected','enable']"><i class="fas fa-plus-circle" aria-hidden></i> Enable</a>
                                <a href="#/" class="dropdown-item" on-click="['applyToSelected','disable']"><i class="fas fa-minus-circle" aria-hidden></i> Disable</a>
                                <a href="#/" class="dropdown-item" on-click="['applyToSelected','activate']"><i class="far fa-check-circle" aria-hidden></i> Activate</a>
                                <a href="#/" class="dropdown-item" on-click="['applyToSelected','deactivate']"><i class="far fa-times-circle" aria-hidden></i> Deactivate</a>
                            </div>
                        </div>
                        <span>[[applyToSelectedMsg]]</span>
                        [[/selectAssignments]]
                    </div>
                </div>

            </div>
            <div class="card-deck mt-3">
                <div class="card">
                    <div class="card-header">Enrolled Students</div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped table-responsive">
                            <thead><tr>
                                <th></th>
                                <th on-click="@.sort('firstname')">First Name</th>
                                <th on-click="@.sort('lastname')">Last Name</th>
                                <th on-click="@.sort('seat')">Seat</th>
                                <th on-click="@.sort('email')">Email</th>
                            </tr></thead>
                            <tbody>
                            [[#students:s]]
                            <tr>
                                <td><a href="{{url('instructor/course/'.$course->id.'/student/[[id]]/details')}}"><i class="fas fa-search" aria-hidden></i><span class="sr-only">Student Details</span></a></td>
                                <td>[[firstname]]</td>
                                <td>[[lastname]]</td>
                                <td>[[pivot.seat]]</td>
                                <td>[[email]]</td>
                            </tr>
                            [[/students]]
                            </tbody>
                        </table>
                    </div>
                </div>
                [[#folders.length > 0]]
                <div class="card" id="files">
                    <div class="card-header">Files</div>
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
            <div class="card-deck mt-3">
                <div class="card">
                    <div class="card-header">Classroom Layout</div>
                    <div class="card-body" id="classCard">
                        <canvas id="classCanvas"></canvas>
                    </div>
                    [[#if noLocation]]
                    <div class="card-footer">
                        Unable to associate student seat with seat location:
                        [[#noLocation:nL]]
                        [[firstname]] [[lastname]] ([[pivot.seat]]),
                        [[/noLocation]]
                    </div>
                    [[/if]]
                </div>
            </div>
        </div>
    </div>
</div>

@include('instructor.course.landingModals')
