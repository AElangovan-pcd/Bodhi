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

            <h3>Peer Review Page for [[course.name]]</h3>

            <div class="alert alert-warning">This is a new experimental feature that is not complete and has no documentation.  If you want to pilot this feature, you are encouraged to talk with Greg first.</div>

            <div class="card-deck mb-2">
                <div class="card">
                    <div class="card-header">Peer Review Tools</div>
                    <div class="card-body">
                        <a class="btn btn-primary btn-sm mb-1" href="create">Create New Peer Review Assignment</a>
                        <button type="button" class="btn btn-light btn-sm mb-1" data-toggle="modal" data-target="#uploadModal">
                            Upload Assignment
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-deck mb-2">
                <div class="card border-success">
                    <div class="card-header">Active Peer Review Assignments</div>
                    <div class="card-body">
                        <ul class="list-group flex-column" id="inactive">
                            [[#active:r]]
                            <li data-id="[[id]]" class="list-group-item d-flex justify-content-between align-items-center">
                                [[name]] ([[status[state]]])
                                <div class="btn-group" role="group" aria-label="First group">
                                    <a href="[[id]]/monitor" role="button" class="btn btn-sm btn-light"><i class="far fa-eye" aria-hidden></i><span class="sr-only">Monitor</span></a>
                                    <a href="{{url('instructor/course/'.$course->id.'/review/[[id]]/edit')}}" role="button" class="btn btn-sm btn-secondary"><i class="far fa-edit" aria-hidden></i><span class="sr-only">Edit</span></a>
                                    <a href="{{url('instructor/course/'.$course->id.'/review/[[id]]/duplicate')}}" role="button" class="btn btn-sm btn-dark"><i class="far fa-copy" aria-hidden></i><span class="sr-only">Duplicate</span></a>
                                    <a href="{{url('instructor/course/'.$course->id.'/review/[[id]]/export')}}" role="button" class="btn btn-sm btn-info"><i class="fas fa-file-export" aria-hidden download></i><span class="sr-only">Export</span></a>
                                </div>
                            </li>
                            [[/active]]
                        </ul>
                    </div>
                </div>
                <div class="card border-danger">
                    <div class="card-header">Inactive Peer Review Assignments</div>
                    <div class="card-body">
                        <ul class="list-group flex-column" id="inactive">
                            [[#inactive:r]]
                            <li data-id="[[id]]" class="list-group-item d-flex justify-content-between align-items-center">
                                [[name]]
                                <div class="btn-group" role="group" aria-label="First group">
                                    <a href="{{url('instructor/course/'.$course->id.'/review/[[id]]/monitor')}}" role="button" class="btn btn-sm btn-light"><i class="far fa-eye" aria-hidden></i><span class="sr-only">Monitor</span></a>
                                    <a href="{{url('instructor/course/'.$course->id.'/review/[[id]]/edit')}}" role="button" class="btn btn-sm btn-secondary"><i class="far fa-edit" aria-hidden></i><span class="sr-only">Edit</span></a>
                                    <a href="{{url('instructor/course/'.$course->id.'/review/[[id]]/duplicate')}}" role="button" class="btn btn-sm btn-dark"><i class="far fa-copy" aria-hidden></i><span class="sr-only">Duplicate</span></a>
                                    <a href="{{url('instructor/course/'.$course->id.'/review/[[id]]/export')}}" role="button" class="btn btn-sm btn-info"><i class="fas fa-file-export" aria-hidden download></i><span class="sr-only">Export</span></a>
                                    <button class="btn btn-sm btn-danger" role="button" data-toggle="modal" data-target="#modal_remove_assignment_[[id]]"><i class="far fa-trash-alt" aria-hidden></i><span class="sr-only">Delete</span></button>
                                </div>
                            </li>
                            [[/inactive]]
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

[[#reviews:i]]
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
                <p>Deleting an assignment will also remove all attached data, including student submissions and reviews.</p>
                <p>Are you sure you want to remove this assignment?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                <a href="{{url('instructor/course/'.$course->id.'/review/[[id]]/delete')}}" class="btn btn-danger">Delete</a>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
[[/reviews]]

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
                <form action="{{url('instructor/course/'.$course->id.'/review/uploadAssignment')}}" method="post" enctype="multipart/form-data">
                @csrf
                <!--<input type="file" name="file">-->
                    <div class="custom-file">
                        <input type="file" accept=".json" id="assignment_import" name="assignment_import" class="custom-file-input" required>
                        <label class="custom-file-label" for="assignment_import">Choose file...</label>
                        <div class="invalid-feedback">Invalid File</div>
                    </div>
                    <button class="btn btn-primary mt-1" type="submit">Upload</button>
                </form>
            </div>
        </div>
    </div>
</div>
