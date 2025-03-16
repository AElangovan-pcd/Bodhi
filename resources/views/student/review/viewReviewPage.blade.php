<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger" role="alert">
                    {{ session('error') }}
                </div>
            @endif
            <h2>[[review.name]]</h2>

            <div class="card-deck">
                <div class="card">
                    <div class="card-header">Instructions</div>
                    <div class="card-body">
                        [[[review.instructions[review.state]]]]
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">Your Submission</div>
                    <div class="card-body">
                        [[#if review.state == 2 ]]
                        <div class="alert alert-success" role="alert">
                            Submissions are open.
                        </div>
                        [[elseif review.state == 9]]
                        <div class="alert alert-success" role="alert">
                            Revision submissions are open.
                        </div>
                        [[elseif review.state == 1]]
                        <div class="alert alert-warning" role="alert">
                            Submissions are not yet open.
                        </div>
                        [[else]]
                        <div class="alert alert-warning" role="alert">
                            Submissions are closed.
                        </div>
                        [[/if]]
                        [[#if submission]]
                        <div class="alert alert-info" role="alert">
                            Your submission: <a href="mySubmission" download>[[submission.filename]]</a><br/>
                            Uploaded: [[submission.updated_at]]<br/>
                            [[#review.options.types]]
                            Manuscript type: [[review.options.typesList[submission.type].name]].  [[review.state == 2 ? 'To change the type, you must upload your file again.' : '']]
                            [[/review.options.types]]
                        </div>
                        [[else]]
                        <div class="alert alert-danger" role="alert">
                            You have not yet uploaded a submission.
                        </div>
                        [[/if]]

                        [[#if review.state == 2 ]]
                        [[#if submission]]
                        You have already uploaded a file.  If you upload another file, it will replace your original submission.
                        [[/if]]

                        [[#review.options.types]]
                        <div class="dropdown mb-2 d-block">
                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                [[#!type]]
                                Select Manuscript Type
                                [[else]]
                                [[review.options.typesList[type].name]]
                                [[/type]]
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                [[#review.options.typesList:t]]
                                <a class="dropdown-item" href="#/" on-click="set_type" id="[[t]]">[[name]]</a>
                                [[/review.options.typesList]]
                            </div>
                        </div>
                        [[/review.options.types]]
                        <form action="submit" method="post" enctype="multipart/form-data">
                            @csrf
                            <input type="text" id="type" name="type" value="[[type]]" hidden>
                            <div class="custom-file">
                                <input type="file" id="assignment_import" name="assignment_import" class="custom-file-input" required>
                                <label class="custom-file-label" for="assignment_import">Choose file...</label>
                                <div class="invalid-feedback">Invalid File</div>
                            </div>
                            [[#if review.options.types && !type]]
                            Choose a manuscript type before uploading
                            [[else]]
                            <button class="btn btn-primary mt-1" type="submit">Upload</button>
                            [[/if]]
                        </form>
                        [[/if]]
                        [[#if review.state > 7 && submission]]
                        <div class="alert alert-success">
                            Peer reviews of your submission are available.
                            <a href="results" class="btn btn-secondary ml-2 btn-sm">View Reviews</a>
                        </div>
                        [[/if]]
                        [[#if review.state >=9 ]]
                        [[#if revision]]
                        <div class="alert alert-info" role="alert">
                            Your revised submission: <a href="myRevision" download>[[revision.filename]]</a><br/>
                            [[#if revision.response_filename]]
                            Your response to reviewers: <a href="myResponse" download>[[revision.response_filename]]</a><br/>
                            [[/if]]
                            Uploaded: [[revision.updated_at]]
                        </div>
                        [[/if]]
                        [[#if !revision.filename]]
                        <div class="alert alert-danger" role="alert">
                            You have not yet uploaded a revised submission.
                        </div>
                        [[/if]]
                        [[/if]]
                        [[#if review.state == 9 ]]
                        [[#if revision.filename]]
                        You have already uploaded a file for your revised submission.  If you upload another file, it will replace your revised submission.
                        [[/if]]
                        <form action="submitRev" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="custom-file">
                                <input type="file" id="revision_import" name="revision_import" class="custom-file-input" required>
                                <label class="custom-file-label" for="revision_import">Choose revised file...</label>
                                <div class="invalid-feedback">Invalid File</div>
                            </div>
                            <button class="btn btn-primary mt-1 mb-2" type="submit">Upload Revised Submission</button>
                        </form>
                        [[#if review.options.response]]
                        [[#if revision.response_filename]]
                        You have already uploaded a file for your response to reviewers.  If you upload another file, it will replace your current response to reviewers.
                        [[else]]
                        <div class="alert alert-danger" role="alert">
                            You have not yet uploaded a response to reviewers.
                        </div>
                        [[/if]]
                        <form action="submitResponse" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="custom-file">
                                <input type="file" id="response_import" name="response_import" class="custom-file-input" required>
                                <label class="custom-file-label" for="response_import">Choose response to reviewers file...</label>
                                <div class="invalid-feedback">Invalid File</div>
                            </div>
                            <button class="btn btn-primary mt-1" type="submit">Upload Response File</button>
                        </form>
                        [[/if]]
                        [[/if]]
                        [[#if review.state == 11 && feedback]]
                        <div class="alert alert-dark" role="alert">
                            You have instructor feedback.  <a href="getFeedback" download>Click to download.</a>
                        </div>
                        [[/if]]
                    </div>
                </div>
                [[#if review.state == 6 && submission]]
                [[^re_render]][[>AcceptingReviews]][[/]]
                [[/if]]

                [[#if review.state >= 10 && submission && jobs.length > 0 && review.options.response && review.options.responseView]]
                [[^re_render]][[>RevisionsClosed]][[/]]
                [[/if]]
            </div>

        </div>
    </div>
</div>

[[#partial AcceptingReviews]]
<div class="card">
    <div class="card-header">Reviewing Tasks</div>
    <div class="card-body">
        <strong>Reviews assigned to you:</strong>
        <ul class="list-group mt-1">
            [[#jobs:j]]
            <a href="complete/[[id]]" class="list-group-item [[complete == true ? 'list-group-item-success' : 'list-group-item-danger']]">Review [[j+1]] [[complete == true ? '(complete)' : '(incomplete)']]</a>
            [[/jobs]]
        </ul>
    </div>
</div>
[[/partial]]

[[#partial ReviewsAvailable]]

[[/partial]]

[[#partial RevisionsClosed]]
<div class="card">
    <div class="card-header">Reviewing Tasks</div>
    <div class="card-body">
        <strong>Responses to your reviews</strong>
        <ul class="list-group mt-1">
            [[#jobs:j]]
            [[#complete]]
            <a href="responseView/[[id]]" class="list-group-item">Review [[j+1]]</a>
            [[else]]
            <div class="list-group-item">Review [[j+1]] (not completed by you)</div>
            [[/complete]]
            [[/jobs]]
        </ul>
    </div>
</div>
[[/partial]]
