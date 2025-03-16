<!-- Modal for layout import -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Classroom Layout Import</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Select a seat file.  It will automatically upload and process.  It will replace all existing seats.  You must save the layout after the import.  If you are unhappy with the import, reload the page without saving.
                <br/>
                <div class="custom-file">
                    <input type="file" accept=".csv" id="csv-file" name="csv-file" class="custom-file-input" required>
                    <label class="custom-file-label" for="validatedCustomFile">Choose file...</label>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Modal for image selection / upload -->
<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">Select Classroom Image</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Select an existing classroom image or upload your own.
                <hr>
                <h5>Upload</h5>
                Image should be square to avoid distortion.
                <form action="{{url('instructor/course/'.$course->id.'/uploadLayoutImage')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <!--<input type="file" name="file">-->
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Description</span>
                            </div>
                            <input type="text" name="name" class="form-control"><br>
                        </div>

                    <div class="custom-file">
                        <input type="file" accept="image/*" id="layoutImage" name="layoutImage" class="custom-file-input" required>
                        <label class="custom-file-label" for="validatedCustomFile">Choose file...</label>
                        <div class="invalid-feedback">Invalid File</div>
                    </div>
                        <button class="btn btn-primary btn-sm mt-1" type="submit">Upload</button>
                </form>
                <hr>
                <h5>Select</h5>
                <ul class="list-group flex-column">
                    <a href="#" class="list-group-item" on-click="template" id="default_classroom.png">Blank Classroom</a>
                    <a href="#" class="list-group-item" on-click="template" id="48_studio_default.png">48-person Studio</a>
                    <a href="#" class="list-group-item" on-click="template" id="64_studio_default.png">64-person Studio</a>
                    [[#classrooms:c]]
                    <div class="list-group-item d-flex justify-content-between align-items-center" style="cursor:pointer;" on-click="template" id="[[filename]]">
                        [[name]]
                        <div class="btn btn-info btn-sm" on-click="remove-template"  id="[[id]]">Remove</div>
                    </div>
                    [[/classrooms]]
                </ul>
            </div>
        </div>
    </div>
</div>
