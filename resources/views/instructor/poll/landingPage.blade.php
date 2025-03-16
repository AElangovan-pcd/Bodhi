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

            <h3>Poll Page for [[course.name]]</h3>
            <p>Only one poll may be in progress at a time. Please complete the poll in
                progress, then activate or re-open a different poll.</p>

            <div class="card-deck">
                <div class="card">
                    <div class="card-header">Poll Tools</div>
                    <div class="card-body">
                        <a class="btn btn-primary btn-sm mb-1" href="create">Create New Poll</a>
                        <a class="btn btn-light btn-sm mb-1" href="all_results">All Poll Results</a>
                        <a href="{{url('instructor/course/'.$course->id.'/polls/export')}}" role="button" class="btn btn-sm btn-info mb-1" download>Export Polls</a>
                        <button type="button" class="btn btn-info btn-sm mb-1" data-toggle="modal" data-target="#uploadModal">
                            Import Polls
                        </button>
                        <div class="btn-group">
                            <button class="btn dropdown-toggle btn-sm" data-toggle="dropdown">Enable copy to: [[copy_title]]<span class="caret"></span></button>
                            <ul class="dropdown-menu">
                                [[#classes:c]]
                                <li><a href="#" id="[[id]]" on-click="setCopy">[[name]]</a></li>
                                [[/classes]]
                            </ul>
                        </div><!-- /btn-group -->
                        <button type="button" class="btn btn-light btn-sm mb-1" on-click="updateOrder" data-toggle="tooltip" data-placement="top" title="Drag and drop the new polls, then click this button to save the order.">
                            Save Poll Order
                        </button>
                    </div>
                </div>
                <div class="card" id="progress">
                    <div class="card-header">Poll In-Progress</div>
                    <div class="card-body">
                        <ul class="list-group">
                            [[#in_progress]]
                            <li class="list-group-item d-flex justify-content-between align-items-center ">
                                [[name]]
                                <div class="btn-group" role="group" aria-label="First group">
                                    <div class="btn btn-sm btn-primary" on-click="results" id="[[id]]" data-toggle="tooltip" data-placement="top" title="Results"><i class="fas fa-clipboard-list" aria-hidden></i><span class="sr-only">Results</span></div>
                                    <div class="btn btn-sm btn-dark" on-click="duplicate" id="[[id]]" data-toggle="tooltip" data-placement="top" title="Duplicate"><i class="far fa-copy" aria-hidden></i><span class="sr-only">Duplicate</span></div>
                                    <div class="btn btn-sm btn-info" on-click="complete_poll" id="[[id]]" data-toggle="tooltip" data-placement="top" title="Close Poll"><i class="far fa-times-circle" aria-hidden></i><span class="sr-only">Close Poll</span></div>
                                </div>
                            </li>
                            [[/in_progress]]
                            [[#if !in_progress]]
                            <li class="list-group-item d-flex justify-content-between align-items-center ">No active poll.</li>
                            [[/if]]
                    </div>
                </div>
            </div>
            <div class="card-deck mt-2">
                <div class="card">
                    <div class="card-header">New Polls</div>
                    <div class="card-body">
                        <ul class="list-group" id="new_polls">
                            [[#new_polls:n]]
                            <li data-id="[[id]]" class="list-group-item d-flex justify-content-between align-items-center">
                                [[name]]
                                <div class="btn-group" role="group" aria-label="First group">
                                    <div class="btn btn-sm btn-secondary" on-click="edit" id="[[id]]" data-toggle="tooltip" data-placement="top" title="Edit"><i class="far fa-edit" aria-hidden></i><span class="sr-only">Edit</span></div>
                                    <div class="btn btn-sm btn-success" on-click="activate" id="[[id]]" data-index="[[n]]" data-toggle="tooltip" data-placement="top" title="Activate"><i class="fas fa-check" aria-hidden></i><span class="sr-only">Activate</span></div>
                                    <div class="btn btn-sm btn-dark" on-click="duplicate" id="[[id]]" data-toggle="tooltip" data-placement="top" title="Duplicate"><i class="far fa-copy" aria-hidden></i><span class="sr-only">Duplicate</span></div>
                                    <div class="btn btn-sm btn-danger" on-click="delete" id="[[id]]" data-toggle="tooltip" data-placement="top" title="Delete"><i class="far fa-trash-alt" aria-hidden></i><span class="sr-only">Delete</span></div>
                                    [[#if ../../copy == 1]]
                                    <div class="btn btn-info btn-sm pull-right" on-click="copy">Copy</div>
                                    [[/if]]
                                </div>
                            </li>
                            [[/new_polls]]
                        </ul>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">Completed Polls</div>
                    <div class="card-body">
                        <ul class="list-group">
                            [[#completed:c]]
                            [[#if complete == 1]]
                            <li class="list-group-item d-flex justify-content-between align-items-center ">
                                [[name]]
                                <div class="btn-group" role="group" aria-label="First group">
                                    <div class="btn btn-sm btn-primary" on-click="results" id="[[id]]" data-toggle="tooltip" data-placement="top" title="Results"><i class="fas fa-clipboard-list" aria-hidden></i><span class="sr-only">Results</span></div>
                                    <div class="btn btn-sm btn-dark" on-click="duplicate" id="[[id]]" data-toggle="tooltip" data-placement="top" title="Duplicate"><i class="far fa-copy" aria-hidden></i><span class="sr-only">Duplicate</span></div>
                                    <div class="btn btn-sm btn-success" on-click="allow_answers" id="[[id]]" data-index="[[c]]" data-toggle="tooltip" data-placement="top" title="Re-Open"><i class="fas fa-check" aria-hidden></i><span class="sr-only">Re-Open</span></div>
                                    <div class="btn btn-sm btn-danger" on-click="delete" id="[[id]]" data-toggle="tooltip" data-placement="top" title="Delete"><i class="far fa-trash-alt" aria-hidden></i><span class="sr-only">Delete</span></div>
                                    [[#if ../../copy]]
                                    <div class="btn btn-info btn-sm pull-right" on-click="copy" id="[[id]]">Copy</div>
                                    [[/if]]
                                </div>
                            </li>
                            [[/if]]
                            [[/completed]]
                        </ul>
                    </div>
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
                Upload a poll xml file here.
                <form action="{{url('instructor/course/'.$course->id.'/polls/import')}}" method="post" enctype="multipart/form-data">
                @csrf
                <!--<input type="file" name="file">-->
                    <div class="custom-file">
                        <input type="file" accept=".xml" id="assignment_import" name="assignment_import" class="custom-file-input" required>
                        <label class="custom-file-label" for="assignment_import">Choose file...</label>
                        <div class="invalid-feedback">Invalid File</div>
                    </div>
                    <button class="btn btn-primary mt-1" type="submit">Upload</button>
                </form>
            </div>
        </div>
    </div>
</div>
