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
            @if(isset($student_name))
                <div class="alert alert-info" role="alert">
                    Viewing results for {{ $student_name }}.
                </div>
            @endif

            <div class="card-deck">
                <div class="card">
                    <div class="card-header">Assignment Description</div>
                    <div class="card-body">
                        [[[review.description]]]
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">Tools</div>
                    <div class="card-body">
                        <button type="button" class="btn btn-outline-dark mb-1" data-toggle="modal" data-target="#textModal" on-click="gatherComments">
                            Get Written Comments
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-group mt-2">
                [[#jobs:j]]
                <div class="card">
                    <div class="card-header">Review [[j+1]]</div>
                    <div class="card-body">
                        [[#!complete]]Review not submitted.
                        [[else]]
                        [[#answers:a]]
                            [[#if ~/questions[a].type == 0]]
                                [[^re_render]][[>MCQuestion]][[/]]
                            [[elseif ~/questions[a].type == 1]]
                                [[^re_render]][[>RatingQuestion]][[/]]
                            [[elseif ~/questions[a].type == 2]]
                                [[^re_render]][[>TextQuestion]][[/]]
                            [[/if]]
                                [[#if a<answers.length-1]]<hr>[[/if]]
                        [[/answers]]
                        [[/complete]]
                    </div>
                </div>
                [[/jobs]]
            </div>
        </div>
    </div>
</div>

[[#partial MCQuestion]]
[[[~/questions[a].description]]]
[[#~/questions[a].choices:c]]
<div class="form-check disabled">
    <input class="form-check-input i" type="radio" value="[[c]]" name="multiple.[[j]][[a]]" q-data="[[a]]" id="[[a]]" on-click="select" [[answers[a].selected === c ? 'checked' : '']] disabled>
    <label class="form-check-label" for="[[c]]">
        [[name]]
    </label>
</div>
[[/~/questions[a].choices]]
[[/partial]]

[[#partial RatingQuestion]]
[[[~/questions[a].description]]]
[[#~/questions[a].choices:c]]
<div class="form-check disabled">
    <input class="form-check-input i" type="radio" value="[[c]]" name="multiple.[[j]][[a]]" q-data="[[a]]" id="[[a]]" on-click="select" [[answers[a].selected === c ? 'checked' : '']] disabled>
    <label class="form-check-label" for="[[c]]">
        [[name]]
    </label>
</div>
[[/~/questions[a].choices]]
[[/partial]]

[[#partial TextQuestion]]
[[[~/questions[a].description]]]
[[[text]]]
[[/partial]]

<!-- Modal for text comments -->
<div id="textModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Comments</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>You can copy and paste this to create a response to reviewers:</p>
                <textarea class ="form-control" id="modal_text"
                          value="[[comments]]">
                        </textarea>
                <br><p></p>
            </div>
        </div>
    </div>
</div>
