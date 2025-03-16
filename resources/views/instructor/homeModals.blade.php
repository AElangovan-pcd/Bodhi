[[#inactive:i]]
<!-- Modal to confirm the removal of a course-->
<div class="modal fade" id="modal_remove_course_[[id]]">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeModalLabel">Warning</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Deleting a course will also remove all attached data, including student scores and answers.</p>
                <p>Are you sure you want to remove this course?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                <a href="{{url('instructor/course/[[id]]/delete')}}" class="btn btn-danger">Delete</a>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
[[/inactive]]

<!-- Modal for changing name -->
<div class="modal fade" id="nameModal" tabindex="-1" role="dialog" aria-labelledby="nameModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nameModalLabel">Change Name</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{url('/changeName')}}" method="post">
                    @csrf
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1">First</span>
                        </div>
                        <input type="text" name="new_firstname" value="{{$user->firstname}}" id="new_firstname">
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1">Last</span>
                        </div>
                        <input type="text" name="new_lastname" value="{{$user->lastname}}" id="new_lastname">
                    </div>
                    <button action="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for learning about new feature -->
<div class="modal fade" id="learnModal" tabindex="-1" role="dialog" aria-labelledby="nameModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nameModalLabel">New Feature</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    The optional assignment inline-chat feature allows students to discuss questions directly inside the assignment.
                    By default, this feature is OFF unless turned on by the instructor in the assignment. It must be enabled on a per-assignment basis by editing the assignment and checking the box in the assignment settings area, then saving the assignment.
                </p>
                <p>
                    Enabling this feature will add a button to all questions (except graded written questions) that will allow users to display the discussion and add comments.
                </p>
                <p>
                    Students will not know each other's identities, but the instructor will be able to see the identity of the student who posted each comment.
                    Currently, students cannot delete any comments they make, but instructors can delete any comments.
                </p>
                <p>
                    The setting for inline-chat is currently not preserved on assignment export/import, so it will be off by default on imported assignments and must be manually enabled for each assignment.
                </p>
            </div>
        </div>
    </div>
</div>