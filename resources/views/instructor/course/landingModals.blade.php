[[#assignments:i]]
<!-- Modal to confirm the removal of an assignment-->
<div class="modal fade" id="modal_remove_assignment_[[id]]">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeModalLabel">Warning</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Deleting an assignment will also remove all attached data, including student scores and answers.</p>
                <p>Are you sure you want to remove this assignment?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                <a href="{{url('instructor/course/'.$course->id.'/assignment/[[id]]/delete')}}" class="btn btn-danger">Delete</a>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
[[/assignments]]

<!-- Modal for changing course name -->
<div class="modal fade" id="nameModal" tabindex="-1" role="dialog" aria-labelledby="nameModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nameModalLabel">Change Course Name</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{url('instructor/course/'.$course->id.'/changeName')}}" method="post">
                    @csrf
                    <input type="text" name="new_name" value="{{$course->name}}" id="new_name">
                    <button action="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for changing course details -->
<div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">Edit Course Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="course-name">Course Name</span>
                    </div>
                    <input type="text" class="form-control" placeholder="Course Name" aria-label="Course Name" aria-describedby="course-name" value="[[course.name]]">
                </div>
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="course-key">Course Key</span>
                    </div>
                    <input type="text" class="form-control" placeholder="Course Key" aria-label="Course Key" aria-describedby="course-key" value="[[course.key]]">
                    <div class="input-group-append">
                        <span class="input-group-text"><i class="far fa-question-circle" aria-hidden data-toggle="tooltip" data-placement="right" title="The key is something that students need to know in order to join the course."></i>
                        <span class="sr-only">The key is something that students need to know in order to join the course.</span></span>
                    </div>
                </div>
                <strong>Course Description</strong> <i class="far fa-question-circle" aria-hidden data-toggle="tooltip" data-placement="right" title="If not blank, the course description will appear near the top of the student's course landing page. You can include contact information, schedule, etc."></i>
                <span class="sr-only">The key is something that students need to know in order to join the course.</span>
                <div id="course_description">[[[course.description]]]</div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" value="response" id="progress_display" [[course.progress_display ? 'checked' : '']] on-click="@.toggle('course.progress_display')">
                    <label class="form-check-label" for="progress_display">
                        Progress Display
                        <i class="far fa-question-circle" aria-hidden data-toggle="tooltip" data-placement="right" title="Shows bars on assignment list showing submission and score completion for each assignment."></i>
                        <span class="sr-only">Shows bars on assignment list showing submission and score completion for each assignment.</span>
                    </label>
                </div>
                <div class="mt-2">
                    <button class="btn btn-sm btn-primary" on-click="update_details">Save Changes</button> [[update_details_msg]]
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for changing course key -->
<div class="modal fade" id="keyModal" tabindex="-1" role="dialog" aria-labelledby="keyModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="keyModalLabel">Change Course Key</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                The course key is something that a student must know to register for the course.
                <form action="{{url('instructor/course/'.$course->id.'/changeKey')}}" method="post">
                    @csrf
                    <input type="text" name="new_key" value="{{$course->key}}" id="new_key">
                    <button action="submit" class="btn btn-primary">Save</button>
                </form>
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
                Upload an assignment file here.
                <form action="{{url('instructor/course/'.$course->id.'/uploadAssignment')}}" method="post" enctype="multipart/form-data">
                @csrf
                <!--<input type="file" name="file">-->
                    <div class="custom-file">
                        <input type="file" accept=".xlsx,.xml" id="assignment_import" name="assignment_import[]" class="custom-file-input" required multiple>
                        <label class="custom-file-label" for="assignment_import">Choose file...</label>
                        <div class="invalid-feedback">Invalid File</div>
                    </div>
                    <button class="btn btn-primary mt-1" type="submit">Upload</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for course linking -->
<div class="modal fade" id="linkingModal" tabindex="-1" role="dialog" aria-labelledby="keyModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="linkingModalLabel">Course Linking</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                [[#if course.parent_course_id != null]]
                [[>child_course]]
                [[else]]
                [[#if !course.link_requests_outgoing.length>0]]
                <p>Course listed as linkable parent: [[course.linkable ? 'Yes' : 'No']]
                    <button class="btn btn-sm btn-outline-dark" on-click="toggleLinkable">[[linkableMsg ? linkableMsg : 'Toggle']]</button>
                </p>
                <hr>
                [[/if]]
                [[#course.linkable]]
                <h5>Requests to Link to this Course</h5>
                <ul class="list-group">
                    [[#course.link_requests_incoming:lri]]
                    <li class="list-group-item">
                        [[child_course.name]]
                        <span class="float-right">
                            [[~/linkRequestMsgs[lri]]]
                            <button class="btn btn-sm btn-success" on-click="['makeLinkRequest','incoming','accept',child_course.id,lri]">Accept</button>
                            <button class="btn btn-sm btn-danger" on-click="['makeLinkRequest','incoming','reject',child_course.id,lri]">Reject</button>
                        </span>
                    </li>
                    [[/course.link_requests_incoming]]
                </ul>
                <hr>
                [[else]]
                [[#linkableCourses.length>0]]
                [[#course.link_requests_outgoing.length>0]]
                <h5>Outstanding Request</h5>
                <p>This is a request to link to another course. This course will be the child course. The other course must accept the request.</p>
                <ul class="list-group">
                    [[#course.link_requests_outgoing:lro]]
                    <li class="list-group-item">
                        [[parent_course.name]]
                        <span class="float-right">
                            [[~/linkRequestMsg]]
                            <button class="btn btn-sm btn-danger" on-click="['makeLinkRequest','outgoing','withdraw',parent_course.id]">Withdraw</button>
                        </span>
                    </li>
                    [[/course.link_requests_outgoing]]
                </ul>
                [[else]]
                <h5>Request to link to Another Course</h5>
                <p>This will request to be the child of another course. Only active courses are shown.</p>
                <div class="dropdown d-inline-block">
                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        [[linkRequestCourse != undefined ? linkableCourses[linkRequestCourse].name : 'Select']]
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        [[#linkableCourses:lc]]
                        <a class="dropdown-item" href="#/" on-click="@.set('linkRequestCourse',lc)">[[name]]</a>
                        [[/linkableCourses]]
                    </div>
                </div>
                [[#linkRequestCourse != undefined]]
                <button class="btn btn-sm btn-outline-dark" on-click="['makeLinkRequest','outgoing','request']">Request</button> [[linkRequestMsg]]
                [[/linkRequestCourse]]
                [[/course.link_requests_outgoing.length]]
                [[else]]
                <div class="alert alert-warning">No courses are listed as linkable.</div>
                [[/linkableCourses.length]]
                [[/course.linkable]]
                [[#course.linked_courses.length > 0]]
                <h5>Linked Courses</h5>
                <p>These are child courses that will inherit some properties of this course.</p>
                <ul class="list-group">
                    [[#course.linked_courses:lc]]
                    <li class="list-group-item">
                        <a href="../[[id]]/landing" target="_blank">[[name]]</a>
                        <span class="float-right">
                            [[~/linkedMsgs[lc]]]
                            [[#unlink]]<button class="btn btn-sm btn-outline-danger" on-click="['makeLinkRequest','unlink_child','unlink_child',id,lc]">Confirm Unlink</button>[[/unlink]]
                            <button class="btn btn-sm btn-danger" on-click="@.set('course.linked_courses.'+lc+'.unlink',true)">Unlink</button>
                        </span>
                    </li>
                    [[/course.linked_courses]]
                </ul>
                [[/course.linked_courses.length]]
                [[/if]]
            </div>
        </div>
    </div>
</div>

[[#partial child_course]]
<h5>Linked</h5>
<p>This course is linked to the following course and will inherit some of its properties.</p>
<ul class="list-group">
    <li class="list-group-item">
        <a href="../[[course.linked_parent_course.id]]/landing" target="_blank">[[course.linked_parent_course.name]]</a>
        <span class="float-right">
            [[linkedParentMsg]]
            [[#course.unlink]]<button class="btn btn-sm btn-outline-danger" on-click="['makeLinkRequest','unlink_parent','unlink_parent',course.parent_course_id]">Confirm Unlink</button>[[/course.unlink]]
            <button class="btn btn-sm btn-danger" on-click="@.set('course.unlink',true)">Unlink</button>
        </span>
    </li>
</ul>
[[/partial]]
