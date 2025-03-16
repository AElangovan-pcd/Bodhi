<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-success" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <h3>Scheduler Page for [[course.name]]</h3>

            <div class="alert alert-warning">This is a new experimental feature that is not complete and has no documentation.  If you want to pilot this feature, you are encouraged to talk with Greg first.</div>

            <div class="card-deck mb-2">
                <div class="card">
                    <div class="card-header">
                        Assignments
                    </div>
                    <div class="card-body">
                    <ul class="list-group mb-1">
                        [[#schedules:s]]
                        [[#!deleted]]

                        <li class="list-group-item [[completed ? 'list-group-item-secondary' : '']]" [[completed && !~/showCompleted ? 'style="display:none"' : '']] >
                            <div class="btn-group">
                                <div class="dropdown">
                                    <button class="btn btn-secondary  btn-sm dropdown-toggle [[completed ? 'disabled' : '']]" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        [[assignments[getAssignmentIndex(details.assignment_id)].name]]
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        [[#assignments:i]]
                                        <div class="dropdown-item" on-click="schedule_assignment" id="[[id]]">[[name]]</div>
                                        [[/assignments]]
                                    </div>
                                </div>
                                <div class="dropdown ml-1">
                                    <button class="btn btn-secondary  btn-sm dropdown-toggle [[completed ? 'disabled' : '']]" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        [[details.property]]
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
                                        <div class="dropdown-item" on-click="schedule_property" id="Active">Active</div>
                                        <div class="dropdown-item" on-click="schedule_property" id="Disabled">Disabled</div>
                                        <div class="dropdown-item" on-click="schedule_property" id="Release Deferred">Release Deferred</div>
                                    </div>
                                </div>
                                <div class="dropdown ml-1">
                                    <button class="btn btn-secondary  btn-sm dropdown-toggle [[completed ? 'disabled' : '']]" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        [[details.state == true ? 'On' : 'Off']]
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
                                        <div class="dropdown-item" on-click="schedule_state" id="true">On</div>
                                        <div class="dropdown-item" on-click="schedule_state" id="false">Off</div>
                                    </div>
                                </div>
                                [[#if assignments[getAssignmentIndex(details.assignment_id)].linked_assignments_count>0]]
                                <div class="dropdown ml-1">
                                    <button class="btn btn-secondary  btn-sm dropdown-toggle [[completed ? 'disabled' : '']]" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        [[details.linked == true ? 'Apply to Linked' : 'Do Not Apply to Linked']]
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
                                        <div class="dropdown-item" on-click="schedule_linked" id="true">Apply to Linked</div>
                                        <div class="dropdown-item" on-click="schedule_linked" id="false">Do Not Apply to Linked</div>
                                    </div>
                                </div>
                                [[/if]]
                            </div>
                            <input id="time[[s]]" name="[[s]]" class="flatpickr" data-id="schedule_cal" type="text" placeholder="Select Date..">
                            <div class="btn btn-danger btn-sm" id=[[s]] on-click="remove_schedule">Remove</div>
                            [[#completed]]<div class="btn btn-info btn-sm" id=[[s]] on-click="re-enable_schedule">Re-enable</div>[[/completed]]
                        </li>

                        [[/deleted]]
                        [[/schedules]]
                    </ul>
                    [[#unsavedSchedules]]
                    <div class="alert alert-warning">You have unsaved schedule information.  Reload to abandon changes.</div>
                    [[/unsavedSchedules]]
                    <div class="btn btn-sm btn-info" on-click="add_schedule">Add action</div>
                    <div class="btn btn-sm btn-info" on-click="save_schedule">[[save_sch_msg]]</div>
                    <div class="btn btn-sm btn-outline-dark" data-toggle="modal" data-target="#scheduleHelp">Help</div>
                    <div class="btn btn-sm btn-outline-dark" on-click="@this.toggle('showCompleted')">[[~/showCompleted ? 'Hide ' : 'Show ']]Completed</div>
                    </div>
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
                You can program any number of scheduled actions to automatically change the states of assignments.
                <h5>Important Notes</h5>
                <ul class="list-group">
                    <li class="list-group-item">The scheduler checks every minute for tasks that have not yet been completed.</li>
                    <li class="list-group-item">The scheduler checks for any scheduled action that is older than the current time.  If you schedule an action in the past, it will get run at the next opportunity.</li>
                    <li class="list-group-item">You are responsible for choosing a schedule that makes sense, so proofread your schedule carefully.  In order to be flexible, there is no logic that checks you do anything in a logical order.  This may allow you to do things you don't want to do!</li>
                </ul>
            </div>
        </div>
    </div>
</div>
