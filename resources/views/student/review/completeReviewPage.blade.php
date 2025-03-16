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
                        [[[review.instructions[6]]]]
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">Submission to Review</div>
                    <div class="card-body">
                        <div class="card-text">
                            Please click the button below to download the submission for you to review.  Then answer the questions below and click the button at the bottom to save your review.
                        </div>
                        <div class="card-text">
                            You will be able to update your answers until the review deadline.
                        </div>
                        <a class="btn btn-info mt-2" href="[[job_id]]/download" download>Download Submission</a>
                    </div>
                </div>
            </div>

            [[#review.questions:q]]

            [[#if type == 0 ]]

            [[^re_render]][[>MCQuestion]][[/]]

            [[elseif type == 1 ]]

            [[^re_render]][[>RatingQuestion]][[/]]

            [[elseif type == 2 ]]

            [[^re_render]][[>TextQuestion]][[/]]

            [[/if]]

            [[/review.questions]]

            <div class="card text-center mt-2" id="button_card">
                <div class="card-body">
                    [[#save_error]]
                    <div class="alert alert-danger mb-2">[[save_error]]</div>
                    [[/save_error]]
                    <button type="button" class='btn btn-info mt-1' on-click="save">[[save_msg]]</button>
                </div>
            </div>

        </div>
    </div>
</div>

[[#partial MCQuestion]]
<div class="card mt-2 [[incomplete && required == true ? 'border-danger' : '']]">
    <div class="card-body ">
        [[[description]]]
        [[#choices:c]]
        <div class="form-check">
            <input class="form-check-input" type="radio" value="[[c]]" name="multiple.[[q]]" q-data="[[q]]" id="[[c]]" on-click="select" [[answers[0].selected === c ? 'checked' : '']]>
            <label class="form-check-label" for="[[c]]">
                [[name]]
            </label>
        </div>
        [[/choices]]
    </div>
    [[#required]]<div class="card-footer">*Required</div>[[/required]]
</div>
[[/partial]]

[[#partial RatingQuestion]]
<div class="card mt-2 [[incomplete && required == true ? 'border-danger' : '']]">
    <div class="card-body">
        [[[description]]]
        [[#choices:c]]
        <div class="form-check">
            <input class="form-check-input" type="radio" name="[[../q]]" q-data="[[q]]" value="[[c]]" id="[[c]]" on-click="select" [[answers[0].selected === c ? 'checked' : '']]>
            <label class="form-check-label" for="[[c]]">
                [[name]]
            </label>
        </div>
        [[/choices]]
    </div>
    [[#required]]<div class="card-footer">*Required</div>[[/required]]
</div>
[[/partial]]

[[#partial TextQuestion]]
<div class="card mt-2 [[incomplete && required == true ? 'border-danger' : '']]">
    <div class="card-body tex2jax_ignore">
        [[[description]]]
        <div class='row mt-1'>
            <div class='col-md-12'>
                <div id="text_[[@index]]">[[[answers[0].text]]]</div>
            </div>
        </div>
    </div>
    [[#required]]<div class="card-footer">*Required</div>[[/required]]
</div>
[[/partial]]
