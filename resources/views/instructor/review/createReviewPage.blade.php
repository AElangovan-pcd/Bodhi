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

            <h3>Edit Peer Review Assignment</h3>

            <div class="card-deck">
                <div class="card">
                    <div class="card-header">Assignment Info: [[name]]</div>
                    <div class="card-body tex2jax_ignore">
                        <label for="assignment_name"> Name:</label>
                        <input type="text" class="form-control" id="assignment_name" placeholder="Assignment name" value="[[name]]"> <br>
                        Edit instructions for:
                        <div class="dropdown mb-2 d-inline-block">
                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                [[status[statusEdit]]]
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                [[#status:s]]
                                [[#s!=0 && s!=4 && s!=5]]
                                <a class="dropdown-item" on-click="changeStatus" id="[[s]]">[[this]]</a>
                                [[/s]]
                                [[/status]]
                            </div>
                        </div>
                        <br/>
                        [[#status:s]]
                        <div class="row" [[s != ~/statusEdit ? 'style="display:none;"' : '']]>
                            <div id="instructions_[[s]]" >[[[instructions[s]]]]</div>
                        </div>
                        [[/status]]
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">Assignment Settings</div>
                    <div class="card-body tex2jax_ignore">
                        <label for="info" class="mt-2">Instructor Notes (not visible to students):</label>
                        <div id="info">[[[info]]]</div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="response" id="defaultCheck1"[[options.response ? 'checked' : '']] on-click="response">
                            <label class="form-check-label" for="defaultCheck1">
                                Response to Reviewers?
                            </label>
                        </div>
                        [[#options.response]]
                        <div class="form-check ml-3">
                            <input class="form-check-input" type="checkbox" value="response" id="defaultCheck1"[[options.responseView ? 'checked' : '']] on-click="responseView">
                            <label class="form-check-label" for="defaultCheck1">
                                Responses Visible to Reviewers?
                            </label>
                        </div>
                        [[/options.response]]
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="response" id="defaultCheck1"[[options.types ? 'checked' : '']] on-click="types">
                            <label class="form-check-label" for="defaultCheck1">
                                Use assignment sub-types?
                            </label>
                        </div>
                        [[#options.types]]
                        <strong>Assignment Sub-Types</strong>
                        <div class="btn btn-sm btn-success mb-1" on-click="addType">Add</div>
                        <ul class="list-group" id ="typesList">
                            [[#options.typesList:c]]
                            <li class="list-group-item" id="[[c]]" data-id="[[c]]">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="[[c]]" value="[[name]]">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" on-click="removeChoice" id=[[c]] type="button"><i class="far fa-trash-alt"></i></button>
                                    </div>
                                </div>
                            </li>
                            [[/options.typesList]]
                        </ul>
                        <div class="btn-group btn-group-toggle mt-2" data-toggle="buttons">
                            <label class="btn btn-secondary btn-sm [[options.typesReviewStyle == 0 ? 'active' : '']]" id="0" on-click="typesStyle">
                                <input type="radio" name="options" id="0" autocomplete="off" [[options.typesReviewStyle == 0 ? 'checked' : '']] > Review any type
                            </label>
                            <label class="btn btn-secondary btn-sm [[options.typesReviewStyle == 1 ? 'active' : '']]" id="1" on-click="typesStyle">
                                <input type="radio" name="options" id="1" autocomplete="off" [[options.typesReviewStyle == 1 ? 'checked' : '']] > Review within type
                            </label>
                            <label class="btn btn-secondary btn-sm [[options.typesReviewStyle == 2 ? 'active' : '']]" id="2" on-click="typesStyle">
                                <input type="radio" name="options" id="2" autocomplete="off" [[options.typesReviewStyle == 2 ? 'checked' : '']] > Review across types
                            </label>
                        </div>
                        *Note that review across types will not work correctly with fewer than 3 types or with certain numbers of submissions.
                        [[/options.types]]
                    </div>
                </div>
            </div>

            [[#questions:q]]

            [[#if type == 0 ]]

            [[^re_render]][[>MCQuestion]][[/]]

            [[elseif type == 1 ]]

            [[^re_render]][[>RatingQuestion]][[/]]

            [[elseif type == 2 ]]

            [[^re_render]][[>TextQuestion]][[/]]

            [[/if]]

            [[/questions]]

            <div class="card text-center mt-2" id="button_card">
                <div class="card-body">
                    <button on-click="addQuestion" id="0" class="btn btn-success">Multiple Choice</button>
                    <button on-click="addQuestion" id="1" class="btn btn-success">Rating</button>
                    <button on-click="addQuestion" id="2" class="btn btn-success">Text</button>
                    <br/>
                    <button type="button" class='btn btn-info mt-1' on-click="save">[[saving]]</button>
                </div>
            </div>

        </div>
    </div>
</div>

[[#partial MCQuestion]]
<div class="card mt-2">
    <div class="card-header">
        [[name]] (Multiple Choice)
        <div class="btn-toolbar float-right" role="toolbar" aria-label="Toolbar with button groups">
            <div class="btn-group mr-2" role="group">
                <button class="btn btn-sm btn-outline-secondary" on-click="removeQuestion" id="[[c]]" type="button"><i class="far fa-trash-alt"></i></button>
            </div>
            <div class="btn-group mr-2" role="group">
                <button class="btn btn-sm btn-outline-secondary" on-click="moveQuestion" id="Up" type="button"><i class="fas fa-long-arrow-alt-up"></i></button>
                <button class="btn btn-sm btn-outline-secondary" on-click="moveQuestion" id="Down" type="button"><i class="fas fa-long-arrow-alt-down"></i></button>
            </div>
            <div class="btn-group mr-2" role="group">
                [[#if collapsed != true]]
                <button class="btn btn-sm btn-outline-secondary" id='collapse' on-click="collapseQuestion"><i class="fas fa-compress"></i></button>
                [[else]]
                <button class="btn btn-sm btn-outline-secondary" id='expand' on-click="expandQuestion"><i class="fas fa-expand-arrows-alt"></i></button>
                [[/if]]
            </div>
        </div>
    </div>
    <div class="card-body" [[collapsed == true ? 'style="display:none"' : '']]>
    <div class="input-group">
        <div class="input-group-prepend">
            <span class="input-group-text">Name</span>
        </div>
        <input type="text" class="form-control" id="[[c]]" value="[[name]]">
    </div>
    <div class='row mt-1'>
        <div class='col-md-12'>
            <div id="description_[[@index]]">[[[description]]]</div>
        </div>
    </div>
    <div class="row mt-1">
        <div class='col-md-12'>
            Choices:
            <ul class="list-group" id ="choices_[[q]]">
                [[#choices:c]]
                <li class="list-group-item" id="[[c]]" data-id="[[c]]">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text my-handle"><i class="fas fa-arrows-alt"></i></span>
                        </div>
                        <input type="text" class="form-control" id="[[c]]" value="[[name]]">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" on-click="removeChoice" id=[[c]] type="button"><i class="far fa-trash-alt"></i></button>
                        </div>
                    </div>
                </li>
                [[/choices]]
            </ul>
        </div>
    </div>
</div>
<div class="card-footer" [[collapsed == true ? 'style="display:none"' : '']]>
<button on-click="addChoice" id="q" class="btn btn-success btn-sm">Add Choice</button>
<div class="form-check form-check-inline">
    <input class="form-check-input" type="checkbox" id="[[q]]" value="Required" on-click="check" [[required == true ? 'checked' : '']]>
    <label class="form-check-label" for="inlineCheckbox1">Required</label>
</div>
</div>
</div>
[[/partial]]

[[#partial RatingQuestion]]
<div class="card mt-2">
    <div class="card-header">
        [[name]] (Rating)
        <div class="btn-toolbar float-right" role="toolbar" aria-label="Toolbar with button groups">
            <div class="btn-group mr-2" role="group">
                <button class="btn btn-sm btn-outline-secondary" on-click="removeQuestion" id="[[c]]" type="button"><i class="far fa-trash-alt"></i></button>
            </div>
            <div class="btn-group mr-2" role="group">
                <button class="btn btn-sm btn-outline-secondary" on-click="moveQuestion" id="Up" type="button"><i class="fas fa-long-arrow-alt-up"></i></button>
                <button class="btn btn-sm btn-outline-secondary" on-click="moveQuestion" id="Down" type="button"><i class="fas fa-long-arrow-alt-down"></i></button>
            </div>
            <div class="btn-group mr-2" role="group">
                [[#if collapsed != true]]
                <button class="btn btn-sm btn-outline-secondary" id='collapse' on-click="collapseQuestion"><i class="fas fa-compress"></i></button>
                [[else]]
                <button class="btn btn-sm btn-outline-secondary" id='expand' on-click="expandQuestion"><i class="fas fa-expand-arrows-alt"></i></button>
                [[/if]]
            </div>
        </div>
    </div>
    <div class="card-body" [[collapsed == true ? 'style="display:none"' : '']]>
    <div class="input-group">
        <div class="input-group-prepend">
            <span class="input-group-text">Name</span>
        </div>
        <input type="text" class="form-control" id="[[c]]" value="[[name]]">
    </div>
    <div class='row mt-1'>
        <div class='col-md-12'>
            <div id="description_[[@index]]">[[[description]]]</div>
        </div>
    </div>
    <div class="row mt-1">
        <div class='col-md-12'>
            Choices:
            <ul class="list-group" id ="choices_[[q]]">
                [[#choices:c]]
                <li class="list-group-item" id="[[c]]" data-id="[[c]]">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text my-handle"><i class="fas fa-arrows-alt"></i></span>
                            <span class="input-group-text">Value</span>
                        </div>
                        <input type="text" class="form-control" id="[[c]]" value="[[value]]">
                        <div class="input-group-append">
                            <span class="input-group-text">Name</span>
                        </div>
                        <input type="text" class="form-control" id="[[c]]" value="[[name]]">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" on-click="removeChoice" id=[[c]] type="button"><i class="far fa-trash-alt"></i></button>
                        </div>
                    </div>
                </li>
                [[/choices]]
            </ul>
        </div>
    </div>
</div>
<div class="card-footer" [[collapsed == true ? 'style="display:none"' : '']]>
<button on-click="addChoice" id="q" class="btn btn-success btn-sm">Add Choice</button>
<div class="form-check form-check-inline">
    <input class="form-check-input" type="checkbox" id="[[q]]" value="Required" on-click="check" [[required == true ? 'checked' : '']]>
    <label class="form-check-label" for="inlineCheckbox1">Required</label>
</div>
</div>
</div>
[[/partial]]

[[#partial TextQuestion]]
<div class="card mt-2">
    <div class="card-header">
        [[name]] (Text)
        <div class="btn-toolbar float-right" role="toolbar" aria-label="Toolbar with button groups">
            <div class="btn-group mr-2" role="group">
                <button class="btn btn-sm btn-outline-secondary" on-click="removeQuestion" id="[[c]]" type="button"><i class="far fa-trash-alt"></i></button>
            </div>
            <div class="btn-group mr-2" role="group">
                <button class="btn btn-sm btn-outline-secondary" on-click="moveQuestion" id="Up" type="button"><i class="fas fa-long-arrow-alt-up"></i></button>
                <button class="btn btn-sm btn-outline-secondary" on-click="moveQuestion" id="Down" type="button"><i class="fas fa-long-arrow-alt-down"></i></button>
            </div>
            <div class="btn-group mr-2" role="group">
                [[#if collapsed != true]]
                <button class="btn btn-sm btn-outline-secondary" id='collapse' on-click="collapseQuestion"><i class="fas fa-compress"></i></button>
                [[else]]
                <button class="btn btn-sm btn-outline-secondary" id='expand' on-click="expandQuestion"><i class="fas fa-expand-arrows-alt"></i></button>
                [[/if]]
            </div>
        </div>
    </div>
    <div class="card-body" [[collapsed == true ? 'style="display:none"' : '']]>
    <div class="input-group">
        <div class="input-group-prepend">
            <span class="input-group-text">Name</span>
        </div>
        <input type="text" class="form-control" id="[[c]]" value="[[name]]">
    </div>
    <div class='row mt-1'>
        <div class='col-md-12'>
            <div id="description_[[@index]]">[[[description]]]</div>
        </div>
    </div>
    <div class="row mt-1">
        <div class='col-md-12'>
            A text entry box will be provided.
        </div>
    </div>
</div>
<div class="card-footer" [[collapsed == true ? 'style="display:none"' : '']]>
<div class="form-check form-check-inline">
    <input class="form-check-input" type="checkbox" id="[[q]]" value="Required" on-click="check" [[required == true ? 'checked' : '']]>
    <label class="form-check-label" for="inlineCheckbox1">Required</label>
</div>
</div>
</div>
[[/partial]]
