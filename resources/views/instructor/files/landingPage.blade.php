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

            <h3>Course File Page for [[course.name]]</h3>

            <div class="card-deck mb-2">
                <div class="card">
                    <div class="card-header">File Tools</div>
                    <div class="card-body">
                        <div class="btn btn-primary btn-sm" on-click="addFolder">Create New Folder</div>
                        <div class="btn btn-sm btn-outline-dark" on-click="collapseAll">Collapse All</div>
                        <div class="btn btn-sm btn-outline-dark" on-click="expandAll">Expand All</div>
                    </div>
                </div>
            </div>
            [[#folders:f]]
            <div class="card mt-2">
                <div class="card-header">
                    <div class="row">
                        <div class="col">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Name</span>
                                </div>
                                <input type="text" class="form-control" id="[[c]]" value="[[name]]">
                            </div>
                        </div>
                        <div class="col">
                            <div class="btn-toolbar float-right" role="toolbar" aria-label="Toolbar with button groups">
                                <div class="btn-group mr-2" role="group">
                                    <button class="btn btn-sm btn-outline-secondary" on-click="@.toggle('folders.'+f+'.visible')" id="[[i]]" type="button" data-toggle="tooltip" data-placement="top" title="Toggle Visibility"><i class="[[visible ? 'far fa-eye' : 'far fa-eye-slash']]"></i><span class="sr-only">Toggle Visibility (currently [[visible ? 'visible' : 'invisible']])</span></button>
                                </div>
                                <div class="btn-group mr-2" role="group">
                                    <button class="btn btn-sm btn-outline-secondary" on-click="removeFolder" id="[[c]]" type="button"><i class="far fa-trash-alt"></i></button>
                                </div>
                                <div class="btn-group mr-2" role="group">
                                    <button class="btn btn-sm btn-outline-secondary" on-click="moveFolder" id="Up" type="button"><i class="fas fa-long-arrow-alt-up"></i></button>
                                    <button class="btn btn-sm btn-outline-secondary" on-click="moveFolder" id="Down" type="button"><i class="fas fa-long-arrow-alt-down"></i></button>
                                </div>
                                <div class="btn-group mr-2" role="group">
                                    [[#if collapsed != true]]
                                    <button class="btn btn-sm btn-outline-secondary" id='collapse' on-click="collapseFolder"><i class="fas fa-compress"></i></button>
                                    [[else]]
                                    <button class="btn btn-sm btn-outline-secondary" id='expand' on-click="expandFolder"><i class="fas fa-expand-arrows-alt"></i></button>
                                    [[/if]]
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body" [[collapsed == true ? 'style="display:none"' : '']] >
                    [[#id == -1]]
                        The file structure must be saved to activate this folder.
                    [[else]]
                <div class="row">
                <div class="col-md-8">
                    <ul class="list-group" id ="folder_[[@index]]" folder-id="[[id]]" style="min-height:20px;">
                    [[#course_files:cf]]
                        <li class="list-group-item py-0 px-0" id="[[cf]]" data-id="[[id]]">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text my-handle"><i class="fas fa-arrows-alt"></i></span>
                                    <span class="input-group-text">
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
                                    </span>
                                </div>
                                <input type="text" class="form-control" data-id="[[id]]" id="[[id]]" value="[[name]]">
                                <div class="input-group-append">
                                    <a class="btn btn-outline-secondary" href="download/[[id]]" id=[[cf]] type="button" download><i class="fas fa-file-download"></i></a>
                                    <button class="btn btn-outline-secondary" on-click="updateFile" id=[[cf]] type="button" data-toggle="modal" data-target="#modal_update_file_[[id]]"><i class="fas fa-wrench"></i></button>
                                    <button class="btn btn-outline-secondary" on-click="deleteFile" id=[[cf]] type="button">[[#delMsg]]...[[else]]<i class="far fa-trash-alt"></i>[[/delMsg]]</button>
                                </div>
                            </div>
                        </li>
                    [[/course_files]]
                    </ul>
                </div>
                <div class="col-md"><div id="drop_[[id]]" class="dropzone"></div></div>
                </div>
                    [[/id]]
                </div>
            </div>
        [[/folders]]
            <div class="card text-center mt-2" id="button_card">
                <div class="card-body">
                    <button type="button" class='btn btn-info mt-1' on-click="save">[[saving]]</button>
                </div>
            </div>
        <div class="alert alert-secondary mt-2">Note on folder visibility. Click the eye icon to toggle the folder visibility. Folders marked not visible will not appear on the course landing page and links to files on the course landing page will not be generated. These files <strong>are</strong> still accessible to students if they type in the URL, so it is obscurity, not security. This allows you to refer to these files in assignments without cluttering the course landing page file list.</div>
        </div>
    </div>
</div>

[[#folders:f]]
[[#course_files]]
<!-- Modal to update file-->
<div class="modal fade" id="modal_update_file_[[id]]" data-id="[[id]]">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateModalLabel">File Update</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>[[filename]]</p>
                <p>Drop a file to replace the current file.</p>
                <div><div id="update_[[id]]" class="dropzone"></div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
[[/course_files]]
[[/folders]]
