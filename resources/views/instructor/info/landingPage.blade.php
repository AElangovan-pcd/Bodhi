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

            <h3>Course Information Page for [[course.name]]</h3>

            <div class="card-deck mb-2">
                <div class="card">
                    <div class="card-header">Information Tools</div>
                    <div class="card-body">
                        <div class="btn btn-primary btn-sm mb-1" on-click="addInfo">Create New Information Block</div>
                        <a href="export" class="btn btn-light btn-sm mb-1">Export</a>
                        <button type="button" class="btn btn-light btn-sm mb-1" data-toggle="modal" data-target="#uploadModal">
                            Upload Info
                        </button>
                        <div class="btn btn-sm btn-outline-dark mb-1" on-click="collapseAll">Collapse All</div>
                        <div class="btn btn-sm btn-outline-dark mb-1" on-click="expandAll">Expand All</div>
                        <button type="button" class='btn btn-sm btn-info mb-1' on-click="save">[[saving]]</button>
                        [[#unsaved_updates]]<span class="badge badge-pill badge-info">Unsaved changes</span>[[/unsaved_updates]]
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        Scheduled Actions
                    </div>
                    <div class="card-body">
                    <ul class="list-group mb-1">
                        [[#course.schedules:s]]
                        [[#!deleted]]

                        <li class="list-group-item [[completed ? 'list-group-item-secondary' : '']]" [[completed && !~/showCompleted ? 'style="display:none"' : '']] >
                            <div class="btn-group">
                                <div class="dropdown">
                                    <button class="btn btn-secondary  btn-sm dropdown-toggle [[completed ? 'disabled' : '']]" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        [[infos[getInfoIndex(details.info_id)].title]]
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        [[#infos:i]]
                                        <div class="dropdown-item" on-click="schedule_info" id="[[id]]">[[title]]</div>
                                        [[/infos]]
                                    </div>
                                </div>
                                <div class="dropdown ml-1">
                                    <button class="btn btn-secondary  btn-sm dropdown-toggle [[completed ? 'disabled' : '']]" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        [[details.property]]
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
                                        <div class="dropdown-item" on-click="schedule_property" id="Active">Active</div>
                                        <div class="dropdown-item" on-click="schedule_property" id="Visible">Visible</div>
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
                            </div>
                            <input id="time[[s]]" name="[[s]]" class="flatpickr" data-id="schedule_cal" type="text" placeholder="Select Date..">
                            <div class="btn btn-danger btn-sm" id=[[s]] on-click="remove_schedule">Remove</div>
                            [[#completed]]<div class="btn btn-info btn-sm" id=[[s]] on-click="re-enable_schedule">Re-enable</div>[[/completed]]
                        </li>

                        [[/deleted]]
                        [[/course.schedules]]
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
            [[#infos:i]]
                <div class="card mt-2 tex2jax_ignore [[active ? 'border-success' : '']]">
                    <div class="card-header [[updated || id == -1? 'border-info' : '']]">
                        [[title]]
                        <div class="btn-toolbar float-right" role="toolbar" aria-label="Toolbar with button groups">
                            <div class="btn-group mr-2" role="group">
                                [[#course.linked_courses.length > 0 && id != -1 && !updated]]
                                <button role="button" class="btn btn-sm btn-info" on-click="@.set('push_id',id)" data-toggle-second="tooltip" data-toggle="modal" data-target="#pushModal" data-placement="top" title="Push to Linked Courses"><i class="fas fa-project-diagram" aria-hidden></i><span class="sr-only">Push to Linked Courses. Changes here will not propagate to children.</span></button>
                                [[/course.linked_courses.length]]
                            </div>
                            <div class="btn-group mr-2" role="group">
                                <button class="btn btn-sm btn-outline-secondary" on-click="toggleActive" id="[[i]]" type="button" data-toggle="tooltip" data-placement="top" title="Toggle Active Block"><i class="[[active ? 'far' : 'fas']] fa-lightbulb"></i><span class="sr-only">Toggle Active State (currently [[active ? 'active' : 'inactive']])</span></button>
                            </div>
                            <div class="btn-group mr-2" role="group">
                                <button class="btn btn-sm btn-outline-secondary" on-click="toggleVisible" id="[[i]]" type="button" data-toggle="tooltip" data-placement="top" title="Toggle Visibility"><i class="[[visible ? 'far fa-eye' : 'far fa-eye-slash']]"></i><span class="sr-only">Toggle Visibility (currently [[visible ? 'visible' : 'invisible']])</span></button>
                            </div>
                            <div class="btn-group mr-2" role="group">
                                <button class="btn btn-sm btn-outline-secondary" on-click="removeInfo" id="[[i]]" type="button"><i class="far fa-trash-alt"></i></button>
                            </div>
                            <div class="btn-group mr-2" role="group">
                                <button class="btn btn-sm btn-outline-secondary" on-click="moveInfo" id="Up" type="button"><i class="fas fa-long-arrow-alt-up"></i></button>
                                <button class="btn btn-sm btn-outline-secondary" on-click="moveInfo" id="Down" type="button"><i class="fas fa-long-arrow-alt-down"></i></button>
                            </div>
                            <div class="btn-group mr-2" role="group">
                                [[#if collapsed != true]]
                                <button class="btn btn-sm btn-outline-secondary" id='collapse' on-click="collapseInfo"><i class="fas fa-compress"></i></button>
                                [[else]]
                                <button class="btn btn-sm btn-outline-secondary" id='expand' on-click="expandInfo"><i class="fas fa-expand-arrows-alt"></i></button>
                                [[/if]]
                            </div>
                        </div>
                        <div class="float-right mr-2">
                            [[#if id == -1 || updated]]
                            <span class="badge badge-pill badge-info align-middle">Unsaved changes</span>
                            [[/if]]
                        </div>
                    </div>
                    <div class="card-body" [[collapsed == true ? 'style="display:none"' : '']]>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Title</span>
                        </div>
                        <input type="text" class="form-control" id="[[i]]" value="[[title]]" onkeydown="list_updated([[i]])">
                    </div>
                    <div class='row mt-1 mb-2'>
                        <div class='col-md-12'>
                            <div id="description_[[@index]]">[[[text.description]]]</div>
                        </div>
                    </div>
                    <div class="btn btn-sm btn-info" on-click="addQuiz" id="[[i]]">Add Quiz</div>
                    <div class="btn btn-sm btn-info" data-toggle="collapse" data-target="#fileCollapse_[[i]]">File References</div>
                    <div class="btn btn-sm btn-info" data-toggle="collapse" data-target="#assignmentCollapse_[[i]]">Assignment References</div>
                    <div class="collapse" id="fileCollapse_[[i]]">
                        <div class="card card-body">
                            Select a folder, then click a file to add a link to the editor.
                            <table class="table">
                                <tr>
                                    <td>
                                        <div class="btn-group-vertical">
                                            [[#folders:f]]
                                            <button class="btn [[selected ? 'btn-secondy' : 'btn-outline-secondary']]" on-click="selectFolder" id="[[f]]">[[name]]</button>
                                            [[/folders]]
                                        </div>
                                    </td>
                                    <td>
                                        [[#folders:f]]
                                        [[#infos[i].selected==this.id]]
                                        [[#course_files:cf]]
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
                                        <span on-click="addFile" id="[[id]]" data-id="[[i]]" name="[[name]]">[[name]]</span><br/>
                                        [[/course_files]]
                                        [[/./selected]]
                                        [[/folders]]
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="collapse" id="assignmentCollapse_[[i]]">
                        <div class="card card-body">
                            Select an assignment to add a reference to the editor.
                            [[#assignments:a]]
                            <span on-click="addAssignment" id="[[id]]" data-id="[[i]]" name="[[name]]">[[name]]</span><br/>
                            [[/assignments]]
                        </div>
                    </div>
                </div>
                [[#info_quizzes:iq]]
                <div class="card-footer" [[../../collapsed == true ? 'style="display:none"' : '']]>
                    Quiz closes: <input id="qtime[[iq]]" name="[[iq]]" info-id="[[i]]" class="flatpickr" data-id="quiz_cal" type="text" placeholder="Select Date..">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Quiz Description</span>
                        </div>
                        <input type="text" class="form-control" id="[[iq]]" value="[[description]]" onkeydown="list_updated([[i]])">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" on-click="['removeQuizItems',i]" id=[[iq]] type="button"><i class="far fa-trash-alt"></i></button>
                        </div>
                    </div>
                    [[#info_quiz_questions:iqq]]
                    <div class="card">
                        <div class="card-body">
                            <div class="btn btn-sm [[type == 1 ? 'btn-dark' : 'btn-outline-dark']]" id="1" on-click="setQuestionType">Single Choice</div>
                            <div class="btn btn-sm [[type == 2 ? 'btn-dark' : 'btn-outline-dark']]" id="2" on-click="setQuestionType">Multiple Selection</div>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Question</span>
                                </div>
                                <input type="text" class="form-control" id="[[iqq]]" value="[[description]]" onkeydown="list_updated([[i]])">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" on-click="['removeQuizItems',i]" id=[[iqq]] type="button"><i class="far fa-trash-alt"></i></button>
                                </div>
                            </div>
                            [[#choices:iqqc]]
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Choice [[iqqc+1]]</span>
                                    <button class="btn [[../../answer.selected.includes(iqqc.toString()) === true ? 'btn-success' : 'btn-outline-secondary']]" on-click="select-choice" id=[[iqqc]] type="button"><i class="far [[../../answer.selected.includes(iqqc.toString()) === true ? 'fa-check-square': 'fa-square']]"></i></button>
                                </div>
                                <input type="text" class="form-control" id="[[iqqc]]" value="[[description]]" onkeydown="list_updated([[i]])">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" on-click="['removeQuizItems',i]" id=[[iqqc]] type="button"><i class="far fa-trash-alt"></i></button>
                                </div>
                            </div>
                            [[/choices]]
                            <div class="btn btn-sm btn-info" on-click="addQuestionChoice">Add Choice</div>
                        </div>
                    </div>
                    [[/info_quiz_questions]]
                <div class="btn btn-sm btn-info" on-click="addQuestion">Add Question</div>
                </div>
                [[/info_quizzes]]
            [[/infos]]
                <div class="card text-center mt-2" id="button_card">
                    <div class="card-body">
                        <div class="btn btn-primary btn-sm mb-1" on-click="addInfo">Create New Information Block</div>
                        <div class="btn btn-sm btn-outline-dark mb-1" on-click="collapseAll">Collapse All</div>
                        <div class="btn btn-sm btn-outline-dark mb-1" on-click="expandAll">Expand All</div>
                        <br/>
                        <button type="button" class='btn btn-info mt-1' on-click="save">[[saving]]</button>
                        [[#unsaved_updates]]<br/><span class="badge badge-pill badge-info">Unsaved changes</span>[[/unsaved_updates]]
                    </div>
                </div>
        </div>
    </div>
</div>

<!-- Modal for assignment upload -->
<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">Upload Assignment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Upload an info json file here.
                <form action="{{url('instructor/course/'.$course->id.'/info/uploadInfo')}}" method="post" enctype="multipart/form-data">
                @csrf
                <!--<input type="file" name="file">-->
                    <div class="custom-file">
                        <input type="file" accept=".json" id="info_import" name="info_import" class="custom-file-input" required>
                        <label class="custom-file-label" for="info_import">Choose file...</label>
                        <div class="invalid-feedback">Invalid File</div>
                    </div>
                    <button class="btn btn-primary mt-1" type="submit">Upload</button>
                </form>
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
                You can program any number of scheduled actions to automatically change the states of the information blocks.
                <h5>Important Notes</h5>
                <ul class="list-group">
                    <li class="list-group-item">The scheduler checks every minute for tasks that have not yet been completed.</li>
                    <li class="list-group-item">The scheduler checks for any scheduled action that is older than the current time.  If you schedule an action in the past, it will get run at the next opportunity.</li>
                    <li class="list-group-item">You are responsible for choosing a schedule that makes sense, so proofread your schedule carefully.  In order to be flexible, there is no logic that checks you do anything in a logical order.  This may allow you to do things you don't want to do!</li>
                    <li class="list-group-item">When one block becomes active, the others are no longer active.  The active block is the block open by default on the course landing page. Active blocks are still visible to students.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Modal for pushing -->
<div class="modal fade" id="pushModal" tabindex="-1" role="dialog" aria-labelledby="pushModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pushModalLabel">Push Info Block</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>This will push copies of the info blocks (including info quizzes) to child courses.</p>
                <p><strong>Important Note: </strong>The copies will belong to the child courses and will no longer be linked to this course.
                You will not be able to make changes to the child info blocks after pushing. Pushing additional times will generate additional copies rather than making changes to existing copies.</p>
                <p>Scheduled actions are not pushed, though info quiz due dates are.</p>
                <p>Are you sure you want to push this block to [[course.linked_courses.length]] child courses?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                <a href="pushToLinkedCourses/[[push_id]]" role="button" class="btn btn-info">Push to Children</a>
            </div>
        </div>
    </div>
</div>
