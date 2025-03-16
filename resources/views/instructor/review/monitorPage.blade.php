<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
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

            <h3>Peer Review Status for "[[assignment.name]]"</h3>

            <div class="card mb-2">
                <div class="card-header">Assignment Status</div>
                <div class="card-body">
                    Change Status:
                    <div class="dropdown mb-2 d-inline-block">
                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            [[status[assignment.state]]]
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            [[#status:s]]
                            [[#s!=4 && s!=5]]
                            <a class="dropdown-item" href="changeStatus/[[s]]">[[this]]</a>
                            [[/s]]
                            [[/status]]
                        </div>
                    </div>
                    [[#assignment.state == 3]]
                    <form action="generateReviewers" method="post">
                        @csrf
                        <div class="input-group">
                            <div class="input-group-prepend" id="button-addon4">
                                <span class="input-group-text">Number of reviewers</span>
                            </div>
                            <input type="text" name="reviewNum" class="form-control" value="[[reviewNum]]" placeholder="Number of reviews per submission">
                            <div class="input-group-append">
                                <button class="btn btn-outline-dark" type="submit">Generate Reviewer Assignments</button>
                            </div>
                        </div>
                    </form>
                    [[/assignment.state]]
                    [[#assignment.state == 3 || assignment.state == 10 || assignment.state == 11]]
                    <form action="uploadFeedback" method="post" enctype="multipart/form-data" class="col-md-8 mt-2">
                        @csrf
                        <div class="input-group">
                            <div class="input-group-prepend" id="button-addon4">
                                <span class="input-group-text">Feedback Zip File</span>
                            </div>
                            <div class="custom-file">
                                <input type="file" accept=".zip" id="feedback_import" name="feedback_import" class="custom-file-input" required>
                                <label class="custom-file-label" for="feedback_import">Choose file...</label>
                                <div class="invalid-feedback">Invalid File</div>
                            </div>
                        </div>
                        <button class="btn btn-primary mt-1" type="submit">Upload Feedback</button>
                    </form>
                    [[/assignment.state]]
                    <div class="d-block mt-2"></div>
                    Scheduled actions:
                    <ul class="list-group mb-1">
                        [[#assignment.schedules:s]]
                        [[#!deleted]]
                        <li class="list-group-item">
                            <button class="btn btn-secondary  btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                [[status[state]]]
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                [[#status:st]]
                                [[#st!=4 && st!=5]]
                                <div class="dropdown-item" on-click="schedule_state" id="[[st]]">[[this]]</div>
                                [[/st]]
                                [[/status]]
                            </div>
                            <input id="time[[s]]" name="[[s]]" class="flatpickr" type="text" placeholder="Select Date..">
                            [[#state == 3]]
                            Number Reviewers: <input id="reviewNum[[s]]" name="[[s]]" type="number" value="[[reviewNum]]" style="width:50px">
                            [[/state]]
                            <div class="btn btn-danger btn-sm" id=[[s]] on-click="remove_schedule">Remove</div>
                        </li>
                        [[/deleted]]
                        [[/assignment.schedules]]
                    </ul>
                    [[#unsavedSchedules]]
                    <div class="alert alert-warning">You have unsaved schedule information.  Reload to abandon changes.</div>
                    [[/unsavedSchedules]]
                    <div class="btn btn-sm btn-info" on-click="add_schedule">Add action</div>
                    <div class="btn btn-sm btn-info" on-click="save_schedule">[[save_sch_msg]]</div>
                    <div class="btn btn-sm btn-outline-dark" data-toggle="modal" data-target="#scheduleHelp">Help</div>
                </div>
            </div>

            <div class="card mb-2">
                <div class="card-header">Results</div>
                <div class="card-body">
                    <a href="stats" class="btn btn-sm btn-info">Review Stats</a>

                    <table class="table table-bordered table-striped table-responsive-md mt-2">
                        <thead>
                        <tr>
                            <th on-click="@.sort('firstname')">First Name</th>
                            <th on-click="@.sort('lastname')">Last Name</th>
                            <th on-click="@.sort('seat')">Seat</th>
                            <th></th>
                            <th>Submission <a href="downloadAllSubmissions" ><i class="far fa-file-archive"></i></a></th>
                            <th colspan="[[assignment.reviewNum]]" style="text-align:center;">Review Assignments</th>
                            <th colspan="[[assignment.reviewNum]]" style="text-align:center;">Reviewed By</th>
                            <th>Results</th>
                            <th>Revision <a href="downloadAllRevisions" download><i class="far fa-file-archive"></i></a></th>
                            [[#assignment.options.response]]<th>Response <a href="downloadAllResponses" download><i class="far fa-file-archive"></i></a></th>[[/assignment.options.response]]
                            <th>Instructor Feedback</th>
                        </tr>
                        </thead>
                        <tbody>
                        [[#assignment.students:s]]
                        <tr>
                            <td>[[firstname]]</td>
                            <td>[[lastname]]</td>
                            <td>[[pivot.seat]]</td>
                            <td><div class="btn btn-sm" data-toggle="modal" data-target="#tool_modal_[[s]]"><i class="fas fa-wrench" aria-hidden></i><span class="sr-only">Student Tools</span></div></td>
                            <td class="[[review_submissions.length != 0 ? 'table-success' : 'table-danger']]"><a href="downloadSubmission/[[review_submissions[0].id]]" >[[review_submissions[0].extension]]</a></td>
                            [[#review_submissions.length != 0]]
                            [[#review_jobs:r]]
                            <td class="[[complete == true ? 'table-success' : 'table-danger']]"><a href="complete/[[id]]">[[submission.user.firstname]] [[submission.user.lastname]]</a></td>
                            [[/review_jobs]]
                            [[#review_submissions[0].jobs]]
                            <td class="[[complete == true ? (viewed == true ? 'table-success' : 'table-info') : 'table-danger']]"><a href="complete/[[id]]">[[user.firstname]] [[user.lastname]]</a></td>
                            [[/review_submissions.0.jobs]]
                            <td style="text-align:center;"><a href="studentResults/[[id]]"><i class="fas fa-search" aria-hidden></i><span class="sr-only">Student Results</span></a></td>
                            <td class="[[review_revision_submissions.length != 0 ? 'table-success' : 'table-danger']]"><a href="downloadRevision/[[review_revision_submissions[0].id]]" >[[review_revision_submissions[0].extension]]</a></td>
                            [[#../../options.response]]
                            <td class="[[review_revision_submissions.length != 0 && review_revision_submissions[0].response_filename != null ? 'table-success' : 'table-danger']]"><a href="downloadResponse/[[review_revision_submissions[0].id]]" >[[review_revision_submissions[0].response_extension]]</a></td>
                            [[/../../options.response]]
                            <td class="[[review_feedbacks.length != 0 ? (review_feedbacks[0].viewed == true ? 'table-success' : 'table-info') : 'table-danger']]"><a href="downloadFeedback/[[review_feedbacks[0].id]]" >[[review_feedbacks[0].extension]]</a></td>
                            [[/review_submissions.length]]
                        </tr>
                        [[/assignment.students]]
                        </tbody>
                    </table>
                </div>
            </div>


        </div>
    </div>
</div>

<!-- Modal for help with scheduling -->
<div class="modal fade" id="scheduleHelp" tabindex="-1" role="dialog" aria-labelledby="scheduleHelpLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scheduleHelpLabel">Scheduled Actions</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                You can program any number of scheduled actions to automatically change the state of the review assignment.
                <h5>Important Notes</h5>
                <ul class="list-group">
                    <li class="list-group-item">The scheduler only checks every 5 minutes.  No event will ever run before its scheduled time, but may run up to 5 minutes after its scheduled time.</li>
                    <li class="list-group-item">The scheduler checks for any scheduled action that is older than the current time.  If you schedule an action in the past, it will get run.</li>
                    <li class="list-group-item">You are responsible for choosing a schedule that makes sense, so proofread your schedule carefully.  In order to be flexible, there is no logic that checks you do anything in a logical order.  This may allow you to do things you don't want to do!</li>
                    <li class="list-group-item">If you schedule "Submissions Closed" it will ask you for the number of reviewers.  When the schedule runs, it will automatically build the reviewer matrix.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

[[#assignment.students:s]]
<!-- Modal to confirm the removal of a course-->
<div class="modal fade" id="tool_modal_[[s]]">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeModalLabel">Student Tools</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h4>[[firstname]] [[lastname]]</h4>
                <hr>
                [[#if review_submissions.length>0]]
                This student already has a submission ([[review_submissions[0].filename]]).
                <div class="btn btn-sm btn-danger" on-click="allow_delete" id="[[s]]">Delete Submission</div>
                <p>If you upload another file, it will replace their original submission.</p>
                [[/if]]
                <h5>Upload submission for student.</h5>
                [[#~/assignment.options.types]]
                <div class="dropdown mb-2 d-block">
                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        [[#!~/type]]
                        Select Manuscript Type
                        [[else]]
                        [[~/assignment.options.typesList[type].name]]
                        [[/~/type]]
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        [[#~/assignment.options.typesList:t]]
                        <div class="dropdown-item" on-click="set_type" id="[[t]]">[[name]]</div>
                        [[/~/assignment.options.typesList]]
                    </div>
                </div>
                [[/~/assignment.options.types]]
                <form action="uploadForStudent" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="text" id="type" name="type" value="[[~/type]]" hidden>
                    <input type="text" id="type" name="user_id" value="[[id]]" hidden>
                    <div class="custom-file">
                        <input type="file" id="assignment_import" name="assignment_import" class="custom-file-input" required>
                        <label class="custom-file-label" for="assignment_import">Choose file...</label>
                        <div class="invalid-feedback">Invalid File</div>
                    </div>
                    [[#if ~/assignment.options.types && !~/type]]
                    Choose a manuscript type before uploading
                    [[else]]
                    <button class="btn btn-primary mt-1" type="submit">Upload</button>
                    [[/if]]
                </form>
            </div>
            <div class="modal-footer">
                [[#allow_delete]]
                <a href="deleteStudentSubmission/[[review_submissions[0].id]]" class="btn btn-sm btn-danger" id="[[s]]">Confirm Deletion</a>
                [[/allow_delete]]
                <button type="button" class="btn btn-light" data-dismiss="modal" on-click="clear_delete" id="[[s]]">Cancel</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
[[/assignment.students]]
