<div class="container" id="editorContainer">

    <div class="row justify-content-center">
        <div class="col-md-12">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-warning" role="alert">
                    {{ session('error') }}
                </div>
            @endif
            <h1> Edit Assignment</h1>
            [[#assignment.linked_assignments_count > 0]]
            <div class="alert alert-info text-center">
                <i class="fas fa-link"></i> This is a linked assignment. Any changes will be applied to [[assignment.linked_assignments_count]] children.
            </div>
            [[/assignment.linked_assingments_count]]
            [[#assignment.parent_assignment_id != null]]
            <div class="alert alert-warning text-center">
                <p><i class="fas fa-link"></i> This is a linked assignment. <i class="fas fa-link"></i></p>
                <p>This assignment is the child of an assignment in the course [[assignment.linked_parent_assignment.course.name]]. You can review the assignment here, but you must either directly or unlink the assignment.</p>
                <p>
                    <a class="btn btn-sm btn-outline-dark" role="button" href="../../../[[assignment.linked_parent_assignment.course.id]]/assignment/[[assignment.parent_assignment_id]]/edit">Edit Parent Assignment</a>
                    <button class="btn btn-sm btn-outline-danger" role="button" data-toggle="modal" data-target="#unlink_modal">Unlink Assignment</button>
                </p>
            </div>
            [[/assignment.parent_assignment_id]]
            <button id="show_functions" type="button" class="btn btn-primary btn-sm mb-2" data-toggle="modal" data-target="#functions_list">
                Show Available Functions
            </button>
            <div class="card-deck">
                <div class="card">
                    <div class="card-header">Assignment Info: [[assignment.name]]</div>
                    <div class="card-body tex2jax_ignore">
                        <label for="assignment_name"> Name:</label>
                        <input type="text" class="form-control" id="assignment_name" placeholder="Assignment name" value="[[assignment.name]]" > <br>
                        <label for="description">Description:</label>
                        <div id="description">[[[assignment.description]]]</div>
                        <div class="mt-4">Assignment Closes:
                            <input id="closes_at" class="flatpickr" data-id="closes_at" type="text" placeholder="No End Date...">
                            <button class="btn btn-sm btn-outline-dark" on-click="clear_closes_at">Clear</button>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">Assignment Settings</div>
                    <div class="card-body tex2jax_ignore">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" value="response" id="questionAutoNumbering" [[assignment.options.questionAutoNumbering ? 'checked' : '']] on-click="@.toggle('assignment.options.questionAutoNumbering')">
                            <label class="form-check-label" for="questionAutoNumbering">
                                Auto-number questions.
                                <i class="far fa-question-circle" aria-hidden data-toggle="tooltip" data-placement="right" title="Automatically assigns question numbers."></i>
                                <span class="sr-only">Automatically assigns question numbers.</span>
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" value="response" id="suppressQuestionNames" [[assignment.options.suppressQuestionNames ? 'checked' : '']] on-click="@.toggle('assignment.options.suppressQuestionNames')">
                            <label class="form-check-label" for="suppressQuestionNames">
                                Suppress question names.
                                <i class="far fa-question-circle" aria-hidden data-toggle="tooltip" data-placement="right" title="Prevents display of question names to students."></i>
                                <span class="sr-only">Prevents display of question names to students.</span>
                            </label>
                        </div>
                        <div class="form-check  mb-2">
                            <input class="form-check-input" type="checkbox" value="response" id="defaultCheck1"[[assignment.options.inline_discussion ? 'checked' : '']] on-click="inline_discussion">
                            <label class="form-check-label" for="defaultCheck1">
                                Allow inline discussions.
                                <i class="far fa-question-circle" aria-hidden data-toggle="tooltip" data-placement="right" title="Lets students discuss each question directly inside the assignment."></i>
                                <span class="sr-only">Lets students discuss each question directly inside the assignment.</span>
                            </label>
                        </div>
                        <div class="form-check  mb-2">
                            <input class="form-check-input" type="checkbox" value="response" id="defaultCheck2"[[assignment.options.showExtraEditors ? 'checked' : '']] on-click="show_extra">
                            <label class="form-check-label" for="defaultCheck2">
                                Show extra info editors.
                                <i class="far fa-question-circle" aria-hidden data-toggle="tooltip" data-placement="right" title="Shows editors for extra info fields.  When extra info fields are made available (on a question by question basis), students get a button to expand additional information for the question."></i>
                                <span class="sr-only">Shows editors for extra info fields.  When extra info fields are made available (on a question by question basis), students get a button to expand additional information for the question.</span>
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" value="response" id="showAttemptsCheckbox" [[assignment.options.showAttempts ? 'checked' : '']] on-click="@.toggle('assignment.options.showAttempts')">
                            <label class="form-check-label" for="showAttemptsCheckbox">
                                Show attempts.
                                <i class="far fa-question-circle" aria-hidden data-toggle="tooltip" data-placement="right" title="Displays attempts on questions to students."></i>
                                <span class="sr-only">Displays attempts on questions to students.</span>
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" value="response" id="gradedFilterAllowed" [[assignment.options.gradedFlagFilter ? 'checked' : '']] on-click="@.toggle('assignment.options.gradedFlagFilter')">
                            <label class="form-check-label" for="gradedFilterAllowed">
                                Show graded filter button.
                                <i class="far fa-question-circle" aria-hidden data-toggle="tooltip" data-placement="right" title="Gives students a button to filter the assignment to show only graded questions."></i>
                                <span class="sr-only">Gives students a button to filter the assignment to show only graded questions.</span>
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" value="response" id="deferredOverride" [[assignment.options.deferredOverride ? 'checked' : '']] on-click="@.toggle('assignment.options.deferredOverride')">
                            <label class="form-check-label" for="deferredOverride">
                                All immediate feedback (override deferred).
                                <i class="far fa-question-circle" aria-hidden data-toggle="tooltip" data-placement="right" title="Gives immediate feedback on all questions, including those marked for deferred feedback."></i>
                                <span class="sr-only">Gives immediate feedback on all questions, including those marked for deferred feedback.</span>
                            </label>
                        </div>
                        <button class="btn btn-sm btn-outline-dark" on-click="toggle_mode">Change Mode</button> [[assignment.type == null || assignment.type === 1 ? 'Normal' : 'Quiz']]<br/>
                        <label for="info">Instructor Notes (not visible to students):</label>
                        <div id="info">[[[assignment.info]]]</div>
                    </div>
                </div>
            </div>
            <button class="btn btn-sm btn-outline-dark mt-2" on-click="collapseAll">Collapse All</button>
            <button class="btn btn-sm btn-outline-dark mt-2" on-click="expandAll">Expand All</button>
            <button class="btn btn-sm btn-secondary mt-2" on-click="reorderQuestions" data-toggle="modal" data-target="#question_order_modal">Reorder Questions</button>
            [[#if editing]]
            <button type="button" class='btn btn-info btn-sm mt-2' on-click="save">Update Assignment</button>
            [[else]]
            <button type="button" class='btn btn-info btn-sm mt-2' on-click="save">Save Assignment</button>
            [[/if]]

            [[#assignment.type == 2]]
            <div class="alert alert-danger mt-2">You have selected quiz mode. After saving the assignment, visit the quiz tab under the results to set up the quiz layout and settings.</div>
            [[/assignment.type]]


            [[#each questions:q]]
            <div class="card mt-2 card-default [[id==-1 ? 'border-success' : status == 'delete' ? 'border-danger' : status == 'update' ? 'border-info' : '']]" id="q_[[@index]]_focus">
                <div class="card-header">
                    [[#if ~/assignment.options.questionAutoNumbering]]
                    <button class="btn btn-sm" on-click="@.toggle('questions.'+q+'.options.excludeFromNumbering')" data-toggle="tooltip" title="Click to toggle exclusion from question numbering.">
                        [[#options.excludeFromNumbering]]<del>[[questionNumbers[q] ]])</del>
                        [[else]][[questionNumbers[q] ]])
                        [[/options.excludeFromNumbering]]
                    </button>
                    [[/if]]
                    [[name]] ([[#options.isolated]]<span class="text-danger">Isolated&nbsp;</span>[[/options.isolated]][[types[type]]])
                    [[#status == 'delete']]
                    <span class="badge badge-danger float-right">Deleted</span>
                    [[else]]
                    <button class="btn btn-warning btn-sm left-space" on-click="deleteQuestion">Remove question</button>
                    <button class="btn btn-sm left-space" id='Up' on-click="moveQuestion"><i class="fas fa-long-arrow-alt-up"></i></button>
                    <button class="btn btn-sm left-space" id='Down' on-click="moveQuestion"><i class="fas fa-long-arrow-alt-down"></i></button>
                    <button class="btn btn-sm left-space" on-click="['duplicateQuestion',q]"><i class="far fa-copy"></i></button>
                    <button class="btn btn-sm" on-click="getJSON" data-toggle="modal" data-target="#question_JSON_modal" aria-label="Export JSON"><i class="fas fa-external-link-square-alt" aria-hidden="true"></i></button>
                    [[#if collapsed != true]]
                    <button class="btn btn-sm left-space" id='collapse' on-click="collapseQuestion"><i class="fas fa-compress"></i></button>
                    [[else]]
                    <button class="btn btn-sm" id='collapse' on-click="expandQuestion"><i class="fas fa-expand-arrows-alt"></i></button>
                    [[/if]]
                    [[#if type != UNANSWERED_QUESTION && type != SHORT_ANSWER]]
                    [[#deferred]]
                    <button class="btn btn-sm" on-click="@.toggle('questions.'+q+'.deferred')"><i class="fas fa-hourglass-half" aria-hidden></i><span class="sr-only">Toggle to Immediate Feedback</span></button>
                    <span class="badge badge-danger">Deferred Feedback</span>
                    [[else]]
                    <button class="btn btn-sm" on-click="@.toggle('questions.'+q+'.deferred')"><i class="fas fa-sync-alt" aria-hidden></i><span class="sr-only">Toggle to Deferred Feedback</span></button>
                    <span class="badge badge-primary">Immediate Feedback</span>
                    [[/deferred]]
                    [[/if]]
                    [[#type != UNANSWERED_QUESTION]]
                    <button class="btn btn-sm [[options.gradedFlag ? 'btn-danger' : '']]" on-click="@.toggle('questions.'+q+'.options.gradedFlag')" data-toggle='tooltip' data-placement='right' title="Toggle whether question is flagged as graded."><i class="[[options.gradedFlag ? 'fas' : 'far']] fa-flag" aria-hidden></i><span class="sr-only">No Flag. Toggle to flag as graded.</span></button>
                    <button class="btn btn-sm [[options.completionDisplay ? 'btn-success' : '']]" on-click="@.toggle('questions.'+q+'.options.completionDisplay')" data-toggle='tooltip' data-placement='right' title="Toggle between showing points to student and completion status only.">[[options.completionDisplay ? 'Completion' : 'Points']]</button>
                    [[/type]]
                    [[#if id == -1]]
                    <span class="badge badge-success float-right">New</span>
                    [[elseif status == 'update']]
                    <span class="badge badge-info float-right">Updated</span>
                    [[/if]]
                    [[/if]]
                </div>
                [[#status != 'delete']]
                    [[#if type == SHORT_ANSWER ]]

                    [[^re_render]][[>shortQuestion]][[/]]

                    [[elseif type == SIMPLE_QUESTION ]]

                    [[^re_render]][[>simpleQuestion]][[/]]

                    [[elseif type == SIMPLE_TEXT_QUESTION ]]

                    [[^re_render]][[>simpleTextQuestion]][[/]]

                    [[elseif type ==  STANDARD_QUESTION]]

                    [[^re_render]][[>stdQuestion]][[/]]

                    [[elseif type == UNANSWERED_QUESTION]]

                    [[^re_render]][[>unansweredQuestion]][[/]]

                    [[elseif type == MOLECULE_QUESTION]]

                    [[^re_render]][[>moleculeQuestion]][[/]]

                    [[elseif type == MULTIPLE_CHOICE_QUESTION]]

                    [[^re_render]][[>multipleChoiceQuestion]][[/]]

                    [[elseif type == REACTION_QUESTION]]

                    [[^re_render]][[>reactionQuestion]][[/]]

                    [[/if]]
                [[/if]]

            </div>
            [[/each]]

            <div class="card mt-2" id="button_card">
                <div class="card-body centered">
                    <button class="btn btn-sm btn-outline-dark mb-2" on-click="collapseAll">Collapse All</button>
                    <button class="btn btn-sm btn-outline-dark mb-2" on-click="expandAll">Expand All</button>
                    <br/>
                    <button class="btn btn-sm btn-secondary mb-2" on-click="reorderQuestions" data-toggle="modal" data-target="#question_order_modal">Reorder Questions</button>
                    <br/>
                    <button on-click="addQuestion" class="btn btn-success mb-2">Question</button>
                    <button on-click="addSimple" class="btn btn-success mb-2">Simple Question</button>
                    <button on-click="addSimpleText" class="btn btn-success mb-2">Simple Text Question</button>
                    <button on-click="addShort" class="btn btn-success mb-2">Graded Written Question</button>
                    <button on-click="addUnanswered" class="btn btn-success mb-2">Information Block</button>
                    <button on-click="addMolecule" class="btn btn-success mb-2">Molecule</button>
                    <button on-click="addMultipleChoice" class="btn btn-success mb-2">Multiple Choice</button>
                    <button on-click="addReaction" class="btn btn-success mb-2">Chemical Reaction</button>
                    <button class="btn btn-success mb-2" data-toggle="modal" data-target="#paste_JSON_modal">Add via Paste</button>
                    <br/>
                    [[#if editing]]
                    <button type="button" class='btn btn-info mb-2' on-click="save">Update Assignment</button>
                    [[else]]
                    <button type="button" class='btn btn-info mb-2' on-click="save">Save Assignment</button>
                    [[/if]]
                    [[#assignment.linked_assignments_count > 0]]
                    <div class="alert alert-info text-center mt-2">
                        <i class="fas fa-link"></i> This is a linked assignment. Any changes will be applied to [[assignment.linked_assignments_count]] children.
                    </div>
                    [[/assignment.linked_assingments_count]]
                </div>
            </div>

            <div class="card" id="responseMsgs">
                [[#successMsgs.length>0]]<div class="alert alert-success mb-0">[[#successMsgs:s]][[this]]<br/>[[/successMsgs]]</div>[[/successMsgs.length]]
                [[#errorMsgs.length>0]]<div class="alert alert-danger mb-0">[[#errorMsgs:e]][[this]]<br/>[[/errorMsgs]]</div>[[/errorMsgs.length]]
            </div>

            <div class="card card-default">
                <div class="card-body centered" style="background-color:#f2dede; color:#a94442" id='response' style="display:none"></div>
            </div>

            <div class="card card-default">
                <div id='preview' style="display:none">
                </div>
            </div>

        </div>


        [[#partial stdQuestion]]

            <div class="card-body" [[collapsed == true ? 'style="display:none"' : '']]>
                <label for="assignment_name"> Name:</label>
                <input type="text" id="question_[[@index]]" class="form-control"  value="[[name]]"> <br>

                <label for="description">Description:</label>
                <div class='row'>
                    <div class='col-md-12 tex2jax_ignore'>
                        <div id="description_[[@index]]">[[[description]]]</div>
                    </div>
                </div>

                <div [[~/assignment.options.showExtraEditors == true ? '' : 'style="display:none"']]>
                    <label for="extra" class="mt-2">Extra Info: <span class="badge [[extra.available == true ? 'badge-success' : 'badge-light']]">[[extra.available == true ? 'Available' : 'Not Available']]</span> <div class="btn btn-sm btn-outline-dark" on-click="extra">Toggle</div></label>

                    <div class='row'>
                        <div class='col-md-12 tex2jax_ignore'>
                            <div id="extra_[[@index]]">[[[extra.text]]]</div>
                        </div>
                    </div>
                </div>
                <div class="align-middle mt-1">
                    <button class="btn btn-sm [[options.isolated ? 'btn-danger' : 'btn-outline-success']]" on-click="@.toggle('questions.'+q+'.options.isolated')">
                        [[options.isolated ? 'Isolated' : 'Open']]
                    </button>
                    <i class="far fa-question-circle" aria-hidden data-toggle="tooltip" data-placement="right" title="Open questions share variables across the assignment. Isolated questions do not share variables across the assignment. Be cautious changing this setting if the assignment is already in use. Doing so may cause unexpected/undesirable behavior, particularly with regard to cached values and computed variables."></i>
                    <span class="sr-only">Open questions share variables across the assignment. Isolated questions do not share variables across the assignment. Be cautious changing this setting if the assignment is already in use. Doing so may cause unexpected/undesirable behavior, particularly with regard to cached values and computed variables.</span>

                </div>
                <h4 class="mt-3">Variables [[#options.isolated]]<span class="text-danger">(Isolated)</span>[[/options.isolated]]</h4>
                [[>variables]]
                <div class='centered'>
                    <button class="btn btn-success btn-sm" on-click="addVariable">Add variable</button>
                    [[#if hasComputed(@index)]]
                    <button class="btn btn-secondary btn-sm" on-click="new_values" on-click="">Allow Value Refresh
                        <span class="badge [[extra.newValues == true ? 'badge-success' : 'badge-light']]">[[extra.newValues == true ? 'Yes' : 'No']]</span>
                    </button>
                    [[/if]]
                </div>

                <h4>Intermediate Variables</h4>
                [[>inter_variables]]
                <div class='centered'>
                    <button class="btn btn-success btn-sm centered" on-click="addIter">Add intermediate variable</button>
                </div>

                <h4>Conditions</h4>
                [[>conditions]]
                <div class='centered'>
                    <button class="btn btn-success btn-sm centered" on-click="addCondition">Add condition</button>
                    <button id="show_functions" type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#functions_list">
                        Show Available Functions
                    </button>
                </div>
            </div>
            [[#if collapsed == true]]
            <div class="card-footer">
                Variables:
                [[#variables:v]]
                    [[name]][[v < variables.length-1 ? ', ' : '']]
                [[/variables]]
                [[#inter_variables:v]]
                , [[name]]
                [[/inter_variables]]
            </div>
            [[/if]]

        [[/partial]]

        [[#partial shortQuestion]]

            <div class="card-body tex2jax_ignore" [[collapsed == true ? 'style="display:none"' : '']]>

                <label for="assignment_name"> Name:</label>
                <input type="text" id="quesiton_[[@index]]" class="form-control"  value="[[name]]"> <br>

                <label for="description">Description:</label>
                <div class='row'>
                    <div class='col-md-12'>
                        <div id="description_[[@index]]">[[[description]]]</div>
                    </div>
                </div>

                <div [[~/assignment.options.showExtraEditors == true ? '' : 'style="display:none"']]>
                    <label for="extra" class="mt-2">Extra Info: <span class="badge [[extra.available == true ? 'badge-success' : 'badge-light']]">[[extra.available == true ? 'Available' : 'Not Available']]</span> <div class="btn btn-sm btn-outline-dark" on-click="extra">Toggle</div></label>

                    <div class='row'>
                        <div class='col-md-12 tex2jax_ignore'>
                            <div id="extra_[[@index]]">[[[extra.text]]]</div>
                        </div>
                    </div>
                </div>

                <div class='row'>
                    <div class='col-md-2'>
                        <label for="[[q]]_points"> Points:</label>
                        <input id="[[q]]_points" type='number' class='form-control' value="[[max_points]]"  />
                    </div>
                    <div class="col-md-4">
                        Options:<br/>
                        <input id="[[q]]_resubmission" type="checkbox" [[options.resubmission === true ? 'checked' : '']] on-click="@.toggle('questions.'+q+'.options.resubmission')">
                        <label for="[[q]]_resubmission"> Resubmission Allowed
                            <a href="#/" class="text-decoration-none" data-toggle='tooltip' data-placement='right' title='Allows students to update their submission until graded or until the assignment is disabled.'><i class="fas fa-question-circle" aria-hidden ></i></a>
                        </label>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-12">
                        <h4>Prewritten Responses : <button on-click="addResponse" class="btn btn-success btn-sm">Add response</button></h4>
                        <table>
                            [[#each responses]]
                            <tr>
                                <td>
                                    <div class="input-group" style="width: 100%">
                                        <textarea class="form-control row-fluid" id="[[html_id]]" style="min-width: 380px"  value="[[response]]"></textarea>
                                    </div>
                                </td>
                                <td>
                                    <button on-click='deleteResponse' class="btn btn-warning btn-sm">Remove</button>
                                </td>
                            </tr>
                            [[/each]]
                        </table>
                    </div>
                </div>
            </div>

        [[/partial]]

        [[#partial simpleQuestion]]

            <div class="card-body tex2jax_ignore" [[collapsed == true ? 'style="display:none"' : '']]>
                <div class="row">
                    <div class="col-sm-5">
                        <div class="form-group mt-3 mb-0">
                            <input type="text" id="question_[[@index]]" class="form-control" value="[[name]]"  />
                            <label for="question_[[@index]]">Name</label>
                        </div>
                        <div class="form-group mb-0">
                            <input type="text" step="any" class="form-control" value="[[answer]]" />
                            <label>Answer</label>
                        </div>
                        <div class="row mb-0">
                            <div class="col-sm-8">
                                <input type="text" step="any" class="form-control"  value="[[tolerance]]" />
                            </div>
                            <div class="col-sm-4">
                                <select value="[[tolerance_type]]" >
                                    <option value="0">Percent</option>
                                    <option value="1">Range</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-8 small-label">Tolerance</div>
                            <div class="col-sm-4 small-label">Type</div>
                        </div>
                    </div>
                    <div class="col-sm-7">
                        <div class="row">
                            <div class="col-sm-7 small-label">Question Text</div>
                        </div>
                        <div id="description_[[@index]]">[[[description]]]</div>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-sm-10">
                        <input class="form-control" type="text" value="[[feedback]]" />
                    </div>
                    <div class="col-sm-2">
                        <input type='number' class='form-control' value="[[max_points]]"  />
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-10 small-label">Feedback</div>
                    <div class="col-sm-2 small-label">Points</div>
                </div>

                <div [[~/assignment.options.showExtraEditors == true ? '' : 'style="display:none"']]>
                    <label for="extra" class="mt-2">Extra Info: <span class="badge [[extra.available == true ? 'badge-success' : 'badge-light']]">[[extra.available == true ? 'Available' : 'Not Available']]</span> <div class="btn btn-sm btn-outline-dark" on-click="extra">Toggle</div></label>

                    <div class='row'>
                        <div class='col-md-12 tex2jax_ignore'>
                            <div id="extra_[[@index]]">[[[extra.text]]]</div>
                        </div>
                    </div>
                </div>

            </div>

        [[/partial]]

        [[#partial simpleTextQuestion]]

            <div class="card-body tex2jax_ignore" [[collapsed == true ? 'style="display:none"' : '']]>
                <div class="row">
                    <div class="col-sm-5">
                        <div class="form-group mt-3 mb-0">
                            <input type="text" id="question_[[@index]]" class="form-control" value="[[name]]"  />
                            <label for="question_[[@index]]">Name</label>
                        </div>
                        <div class="form-group mb-0">
                            <input type="text" step="any" class="form-control" value="[[answer]]" />
                            <label>Answer(s)</label>
                        </div>
                        <span class="mb-0">Case-Sensitive?</span>
                        <select value="[[tolerance_type]]" >
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                    <div class="col-sm-7">
                        <div class="row">
                            <div class="col-sm-7 small-label">Question Text</div>
                        </div>
                        <div id="description_[[@index]]">[[[description]]]</div>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-sm-10">
                        <input class="form-control" type="text" value="[[feedback]]" />
                    </div>
                    <div class="col-sm-2">
                        <input type='number' class='form-control' value="[[max_points]]"  />
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-10 small-label">Feedback</div>
                    <div class="col-sm-2 small-label">Points</div>
                </div>

                <div [[~/assignment.options.showExtraEditors == true ? '' : 'style="display:none"']]>
                    <label for="extra" class="mt-2">Extra Info: <span class="badge [[extra.available == true ? 'badge-success' : 'badge-light']]">[[extra.available == true ? 'Available' : 'Not Available']]</span> <div class="btn btn-sm btn-outline-dark" on-click="extra">Toggle</div></label>

                    <div class='row'>
                        <div class='col-md-12 tex2jax_ignore'>
                            <div id="extra_[[@index]]">[[[extra.text]]]</div>
                        </div>
                    </div>
                </div>

            </div>

        [[/partial]]

        [[#partial unansweredQuestion]]

            <div class="card-body tex2jax_ignore" [[collapsed == true ? 'style="display:none"' : '']]>
                <label for="assignment_name"> Name:</label>
                <div class="row">
                    <div class="col-sm-5">
                        <input type="text" id="question_[[@index]]" class="form-control" value="[[name]]" >
                    </div>
                </div>
                <br>
                <label for="description">Description:</label>
                <div class='row'>
                    <div class='col-md-12'>
                        <div id="description_[[@index]]">[[[description]]]</div>
                    </div>
                </div>

                <div [[~/assignment.options.showExtraEditors == true ? '' : 'style="display:none"']]>
                <label for="extra" class="mt-2">Extra Info: <span class="badge [[extra.available == true ? 'badge-success' : 'badge-light']]">[[extra.available == true ? 'Available' : 'Not Available']]</span> <div class="btn btn-sm btn-outline-dark" on-click="extra">Toggle</div></label>

                    <div class='row'>
                        <div class='col-md-12 tex2jax_ignore'>
                            <div id="extra_[[@index]]">[[[extra.text]]]</div>
                        </div>
                    </div>
                </div>
            </div>

        [[/partial]]

        [[#partial moleculeQuestion]]

            <div class="card-body tex2jax_ignore" [[collapsed == true ? 'style="display:none"' : '']]>
            <div class="alert alert-danger">This question type is in the testing phase. You should be able to try it now, but should test it thoroughly before using with students.</div>
            <label for="assignment_name"> Name:</label>
            <div class="row">
                <div class="col-sm-5">
                    <input type="text" id="question_[[@index]]" class="form-control" value="[[name]]" >
                </div>
            </div>
            <br>
            <label for="description">Description:</label>
            <div class='row'>
                <div class='col-md-12'>
                    <div id="description_[[@index]]">[[[description]]]</div>
                </div>
            </div>

            <div class='row'>
                <div class='col-md-4'>
                    <h4 mt-4>Options</h4>
                    <button id="molecule" type="button" class="btn btn-outline-dark btn-sm mb-2" data-toggle="modal" data-target="#molecule_help">
                        Help
                    </button>
                    <br/>
                    Points: <input style="width: 80px;" type='number' class='form-control'  value="[[max_points]]" />
                    <div class="btn btn-sm btn-outline-dark" on-click="toggleMoleculeEvalType">Evaluation Type</div> [[molecule.evalType]]<br/>
                    <div class="btn btn-sm [[molecule.lonePairs ? 'btn-success' : 'btn-outline-dark']]" on-click="toggleLP">Lone Pairs</div> [[molecule.lonePairs ? 'On' : 'Off']]<br/>
                    [[#molecule.evalType != 'formula']]
                    <div class="btn btn-sm [[molecule.explicitH ? 'btn-success' : 'btn-outline-dark']]" on-click="toggleH">Explicit H</div> [[molecule.explicitH ? 'On' : 'Off']]<br/>
                    <div class="btn btn-sm btn-outline-dark" on-click="toggleMoleculeMatchType">Match Type</div> [[molecule.matchType]]<br/>
                    [[else]]
                    <div class="btn btn-sm btn-outline-dark" on-click="toggleHalogens">Halogens</div> [[molecule.halogens ? 'Terminal Positions Only' : 'Any Position']]<br/>
                    <div class="btn btn-sm btn-outline-dark" on-click="toggleGroupMatchType">Functional Group Match Type</div> [[molecule.groupMatchType]]<br/>
                    [[#molecule.groupMatchType != 'ignore']]
                    <div class="btn btn-sm [[molecule.groups.includes('acid') ? 'btn-success' : 'btn-outline-dark']]" on-click="toggleIncludedGroups" id="acid">Acid</div>
                    <div class="btn btn-sm [[molecule.groups.includes('alcohol') ? 'btn-success' : 'btn-outline-dark']]" on-click="toggleIncludedGroups" id="alcohol">Alcohol</div>
                    <div class="btn btn-sm [[molecule.groups.includes('aldehyde') ? 'btn-success' : 'btn-outline-dark']]" on-click="toggleIncludedGroups" id="aldehyde">Aldehyde</div>
                    <div class="btn btn-sm [[molecule.groups.includes('ester') ? 'btn-success' : 'btn-outline-dark']]" on-click="toggleIncludedGroups" id="ester">Ester</div>
                    <div class="btn btn-sm [[molecule.groups.includes('ether') ? 'btn-success' : 'btn-outline-dark']]" on-click="toggleIncludedGroups" id="ether">Ether</div>
                    <div class="btn btn-sm [[molecule.groups.includes('ketone') ? 'btn-success' : 'btn-outline-dark']]" on-click="toggleIncludedGroups" id="ketone">Ketone</div>
                    <br/>
                    [[/molecule.groupMatchType]]
                    [[/molecule.evalType]]
                    <div class="btn btn-sm btn-outline-dark" on-click="toggleMoleculeEditor">Editor Type</div> [[molecule.editor]]<br/>
                    [[#molecule.matchType=='some' || molecule.evalType=='formula']]Required Structures: <input style="width: 80px;" type='number' class='form-control'  value="[[molecule.structureNum]]" />[[/molecule.matchType]]
                </div>
                <div class='col-md-8'>
                    <div id="molecule_[[@index]]"></div>
                </div>
            </div>

            <!--<div class="btn" on-click="testMolecule" id="[[@index]]">Test</div>-->

            <div [[~/assignment.options.showExtraEditors == true ? '' : 'style="display:none"']]>
            <label for="extra" class="mt-2">Extra Info: <span class="badge [[extra.available == true ? 'badge-success' : 'badge-light']]">[[extra.available == true ? 'Available' : 'Not Available']]</span> <div class="btn btn-sm btn-outline-dark" on-click="extra">Toggle</div></label>

            <div class='row'>
                <div class='col-md-12 tex2jax_ignore'>
                    <div id="extra_[[@index]]">[[[extra.text]]]</div>
                </div>
            </div>
        </div>
        </div>

        [[/partial]]

        [[#partial multipleChoiceQuestion]]

        <div class="card-body tex2jax_ignore" [[collapsed == true ? 'style="display:none"' : '']]>
        <div class="row">
            <div class="col-sm-5">
                <div class="form-group mt-3 mb-0">
                    <input type="text" id="question_[[@index]]" class="form-control" value="[[name]]"  />
                    <label for="question_[[@index]]">Name</label>
                </div>
                <div class="row mb-0">
                    <div class="col-sm-4">
                        <input type='number' id="question_[[@index]]_points" class='form-control' value="[[max_points]]"  />
                        <label for="question_[[@index]]_points">Points</label>
                    </div>
                    <div class="col-sm-8">
                        <select value="[[options.MC.type]]" >
                            <option value="single">Select Single</option>
                            <option value="multiple">Select Multiple</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-0">
                    <div class="col-sm-8">
                        Shuffle Options:
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="question_[[q]]_shuffle_none" name="question_[[q]]_shuffle" value="none" [[options.MC.shuffleType=='none' ? 'checked' : '']] on-click="@.set('questions.'+q+'.options.MC.shuffleType','none')">
                            <label class="form-check-label" for="question_[[q]]_shuffle_none">None</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="question_[[q]]_shuffle_random" name="question_[[q]]_shuffle" value="random" [[options.MC.shuffleType=='random' ? 'checked' : '']] on-click="@.set('questions.'+q+'.options.MC.shuffleType','random')">
                            <label class="form-check-label" for="question_[[q]]_shuffle_random">Random</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="question_[[q]]_shuffle_reverse" name="question_[[q]]_shuffle" value="reverse" [[options.MC.shuffleType=='reverse' ? 'checked' : '']] on-click="@.set('questions.'+q+'.options.MC.shuffleType','reverse')">
                            <label class="form-check-label" for="question_[[q]]_shuffle_reverse">Reverse Only</label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        [[#options.MC.type == 'multiple']]
                        <input class="form-check-input" type="checkbox" value="response" id="question_[[q]]_fractional" [[options.MC.fractional ? 'checked' : '']] on-click="@.toggle('questions.'+q+'.options.MC.fractional')">
                        <label class="form-check-label" for="question_[[q]]_fractional">
                            Basic Partial Credit
                            <i class="far fa-question-circle" aria-hidden data-toggle="tooltip" data-placement="right" title="Gives p/n points for each correct option selected and -p/n points for each incorrect option selected, where n is the number of correct selections and p is the points available. The minimum earned is zero points (no net negative scores)."></i>
                            <span class="sr-only">Gives p/n points for each correct option selected and -p/n points for each incorrect option selected, where n is the number of correct selections and p is the points available.</span>
                        </label>
                        [[/options.MC.type]]
                    </div>
                </div>
            </div>
            <div class="col-sm-7">
                <div class="row">
                    <div class="col-sm-7 small-label">Question Text</div>
                </div>
                <div id="description_[[@index]]">[[[description]]]</div>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-12">
                <ul class="list-group" id="question_[[q]]_choices_list">
                    [[#choices:c]]
                    <li class="list-group-item py-0 p-0" data-id="[[c]]">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text choice-handle"><i class="fas fa-arrows-alt"></i> </span>
                                <button class="btn border border-dark [[../../answer.toString().split(',').includes(id.toString()) === true ? 'btn-success' : 'btn-outline-secondary']]" on-click="['selectMCChoice',id]" id="question_[[q]]_choice_[[c]]_selection" type="button"><i class="far [[../../answer.toString().split(',').includes(id.toString()) === true ? 'fa-check-square': 'fa-square']]"></i></button>
                                [[#if ../../options.MC.shuffleType != 'none']]
                                <button role="button" class="btn [[locked ? 'btn-danger' : 'btn-outline-secondary']]" on-click="@.toggle('questions.'+q+'.choices.'+c+'.locked')"><i class="fas fa-[[locked ? 'lock' : 'unlock-alt']]" aria-hidden="true"></i><span class="sr-only">[[locked ? 'Locked' : 'Unlocked']]</span></button>
                                [[/if]]
                                <button class="btn btn-outline-secondary" on-click="['choiceEditor',q,c]"><i class="far fa-edit" aria-hidden="true"></i><div class="sr-only">Edit in rich text editor</div></button>
                            </div>
                            <input type="text" class="form-control" id="question_[[q]]_choice_[[c]]_description" value="[[description]]" on-keydown="['addMCChoice',q,c]">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" on-click="['removeMCChoice',c]" id="question_[[q]]_choice_[[c]]_remove" type="button"><i class="far fa-trash-alt"></i></button>
                            </div>
                        </div>
                    </li>
                    [[/choices]]
                </ul>
                [[#editingChoice]]
                <div class="text-center">
                    <button class="btn btn-info btn-sm" on-click="['closeChoiceEditor',q]">Close Editor</button>
                </div>

                <div id="question_[[q]]_choiceEditor"></div>
                [[/editingChoice]]
                <div class="centered">
                    <button class="btn btn-sm btn-success" on-click="['addMCChoice',q]">Add Choice</button>
                </div>
                <div class="input-group mt-2">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="q_[[q]]_feedback">Feedback</span>
                    </div>
                    <input type="text" class="form-control" placeholder="Feedback for incorrect answer" value="[[feedback]]" aria-label="Feedback" aria-describedby="q_[[q]]_feedback">
                </div>
            </div>
        </div>

        <div [[~/assignment.options.showExtraEditors == true ? '' : 'style="display:none"']]>
        <label for="extra" class="mt-2">Extra Info: <span class="badge [[extra.available == true ? 'badge-success' : 'badge-light']]">[[extra.available == true ? 'Available' : 'Not Available']]</span> <div class="btn btn-sm btn-outline-dark" on-click="extra">Toggle</div></label>

        <div class='row'>
            <div class='col-md-12 tex2jax_ignore'>
                <div id="extra_[[@index]]">[[[extra.text]]]</div>
            </div>
        </div>
        </div>

        </div>

        [[/partial]]

[[#partial reactionQuestion]]

<div class="card-body tex2jax_ignore" [[collapsed == true ? 'style="display:none"' : '']]>
<div class="alert alert-danger">This is a new question type that has not been tested thoroughly. There may be compatibility issues for some browsers.</div>
<button id="computed" type="button" class="btn btn-outline-dark btn-sm mb-2" data-toggle="modal" data-target="#reaction_help">
    Help
</button>
<br/>
<label for="assignment_name"> Name:</label>
<div class="row">
    <div class="col-sm-5">
        <input type="text" id="question_[[@index]]" class="form-control" value="[[name]]" >
    </div>
</div>
<br>
<label for="description">Description:</label>
<div class='row'>
    <div class='col-md-12'>
        <div id="description_[[@index]]">[[[description]]]</div>
    </div>
</div>

<div class="row mb-0 mt-2">
    <div class="col-sm-4">
        <input type='number' id="question_[[@index]]_points" class='form-control' value="[[max_points]]"  />
        <label for="question_[[@index]]_points">Points</label>
    </div>
    <div class="col-sm-8">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" value="response" id="question_[[q]]_phase" [[options.reaction.phase ? 'checked' : '']] on-click="@.toggle('questions.'+q+'.options.reaction.phase')">
            <label class="form-check-label" for="question_[[q]]_phase">
                Include phase
            </label>
        </div>
        <select value="[[answer.scoringMode]]" >
            <option value="simple">No Partial Credit</option>
            <option value="basic">Basic Partial Credit</option>
            <option value="specified">Specified Partial Credit</option>
        </select>
        <select value="[[answer.feedbackMode]]" >
            <option value="simple">Minimal Feedback</option>
            <option value="directed">Directed Feedback</option>
        </select>
        <select value="[[answer.balanceMode]]" >
            <option value="any">Any Matching Balance Ratio</option>
            <option value="exact">These Exact Coefficients</option>
            <option value="ignore">Ignore Coefficients</option>
        </select>
    </div>
</div>
<div class="row mb-0 mt-2">
    [[#answer.scoringMode==='specified']]
    <div class="col-md-3">
        Scoring Fractions
        <div class="input-group input-group-sm mb-2" id="q_[[q]]_specified_points_balance">
            <div class="input-group-prepend">
                <span class="input-group-text" id="q_[[q]]_specified_points_balance_label">Balance</span>
            </div>
            <input type="text" class="form-control" aria-label="Balance Fraction" aria-describedby="q_[[q]]_specified_points_balance_label" value='[[answer.specified.points.balance]]'>
        </div>
        <div class="input-group input-group-sm mb-2" id="q_[[q]]_specified_points_reactant_formulas">
            <div class="input-group-prepend">
                <span class="input-group-text" id="q_[[q]]_specified_points_reactant_formulas_label">Reactant Formulas</span>
            </div>
            <input type="text" class="form-control" aria-label="Reactant Formulas Fraction" aria-describedby="q_[[q]]_specified_points_reactant_formulas_label" value='[[answer.specified.points.reactant_formulas]]'>
        </div>
        <div class="input-group input-group-sm mb-2" id="q_[[q]]_specified_points_product_formulas">
            <div class="input-group-prepend">
                <span class="input-group-text" id="q_[[q]]_specified_points_product_formulas_label">Product Formulas</span>
            </div>
            <input type="text" class="form-control" aria-label="Product Formulas Fraction" aria-describedby="q_[[q]]_specified_points_product_formulas_label" value='[[answer.specified.points.product_formulas]]'>
        </div>
        [[#options.reaction.phase]]
        <div class="input-group input-group-sm mb-2" id="q_[[q]]_specified_points_reactant_phases">
            <div class="input-group-prepend">
                <span class="input-group-text" id="q_[[q]]_specified_points_reactant_phases_label">Reactant Phases</span>
            </div>
            <input type="text" class="form-control" aria-label="Reactant phases Fraction" aria-describedby="q_[[q]]_specified_points_reactant_phases_label" value='[[answer.specified.points.reactant_phases]]'>
        </div>
        <div class="input-group input-group-sm mb-2" id="q_[[q]]_specified_points_product_phases">
            <div class="input-group-prepend">
                <span class="input-group-text" id="q_[[q]]_specified_points_product_phases_label">Product Phases</span>
            </div>
            <input type="text" class="form-control" aria-label="Product phases Fraction" aria-describedby="q_[[q]]_specified_points_product_phases_label" value='[[answer.specified.points.product_phases]]'>
        </div>
        [[/options.reaction.phase]]
    </div>
    [[/answer.scoringMode]]

    <div class="col-md-9">
        Feedback Statements
        <div class="input-group input-group-sm mb-2" id="q_[[q]]_feedback_correct">
            <div class="input-group-prepend">
                <span class="input-group-text" id="q_[[q]]_feedback_correct_label">Correct</span>
            </div>
            <input type="text" class="form-control" aria-label="Correct Feedback" aria-describedby="q_[[q]]_feedback_correct_label" value='[[answer.feedback.correct]]' placeholder="Correct Feedback">
        </div>
        <div class="input-group input-group-sm mb-2" id="q_[[q]]_feedback_incorrect">
            <div class="input-group-prepend">
                <span class="input-group-text" id="q_[[q]]_feedback_incorrect_label">Incorrect</span>
            </div>
            <input type="text" class="form-control" aria-label="Incorrect Feedback" aria-describedby="q_[[q]]_feedback_incorrect_label" value='[[answer.feedback.incorrect]]' placeholder="Incorrect Feedback">
        </div>
    </div>
</div>

<div class="mt-2"><strong>Reactants</strong></div>
[[#answer.reactants:v]]
[[>formula]]
[[/answer.reactants]]
<button class="btn btn-sm btn-success" on-click="addReactionReactant">Add Reactant</button>

<div class="mt-2"><strong>Products</strong></div>
[[#answer.products:v]]
[[>formula]]
[[/answer.products]]
<button class="btn btn-sm btn-success" on-click="addReactionProduct">Add Product</button>

<div [[~/assignment.options.showExtraEditors == true ? '' : 'style="display:none"']]>
<label for="extra" class="mt-2">Extra Info: <span class="badge [[extra.available == true ? 'badge-success' : 'badge-light']]">[[extra.available == true ? 'Available' : 'Not Available']]</span> <div class="btn btn-sm btn-outline-dark" on-click="extra">Toggle</div></label>

<div class='row'>
    <div class='col-md-12 tex2jax_ignore'>
        <div id="extra_[[@index]]">[[[extra.text]]]</div>
    </div>
</div>
</div>
</div>
<div class="card-footer">
    <div id="q_[[q]]_reaction_preview">preview</div>
</div>

[[/partial]]

[[#partial formula]]
<div class="row">
    <div class="col-md-6">
        <div class="input-group input-group-sm mb-2" id="q_[[q]]_r_[[v]]">
            <div class="input-group-prepend">
                <span class="input-group-text" id="q_[[q]]_r_[[v]]_coefficient">Coefficient</span>
            </div>
            <input type="text" class="form-control" on-keyup="['reaction_preview',q]" aria-label="Coefficient" aria-describedby="q_[[q]]_r_[[v]]_coefficient" value='[[coefficient]]'>
            <div class="input-group-prepend">
                <span class="input-group-text" id="q_[[q]]_r_[[v]]_formula">Formula</span>
            </div>
            <input type="text" class="form-control" on-keyup="['reaction_preview',q]" aria-label="Formula" aria-describedby="q_[[q]]_r_[[v]]_formula" value='[[formula]]'>
            <div class="input-group-append">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">[[chargeDefault(charge)]]</button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="#/" on-click="['formula_charge',q,r,'7-']">7-</a>
                    <a class="dropdown-item" href="#/" on-click="['formula_charge',q,r,'6-']">6-</a>
                    <a class="dropdown-item" href="#/" on-click="['formula_charge',q,r,'5-']">5-</a>
                    <a class="dropdown-item" href="#/" on-click="['formula_charge',q,r,'4-']">4-</a>
                    <a class="dropdown-item" href="#/" on-click="['formula_charge',q,r,'3-']">3-</a>
                    <a class="dropdown-item" href="#/" on-click="['formula_charge',q,r,'2-']">2-</a>
                    <a class="dropdown-item" href="#/" on-click="['formula_charge',q,r,'-']">-</a>
                    <a class="dropdown-item" href="#/" on-click="['formula_charge',q,r,null]">0</a>
                    <a class="dropdown-item" href="#/" on-click="['formula_charge',q,r,'+']">+</a>
                    <a class="dropdown-item" href="#/" on-click="['formula_charge',q,r,'2+']">2+</a>
                    <a class="dropdown-item" href="#/" on-click="['formula_charge',q,r,'3+']">3+</a>
                    <a class="dropdown-item" href="#/" on-click="['formula_charge',q,r,'4+']">4+</a>
                    <a class="dropdown-item" href="#/" on-click="['formula_charge',q,r,'5+']">5+</a>
                    <a class="dropdown-item" href="#/" on-click="['formula_charge',q,r,'6+']">6+</a>
                    <a class="dropdown-item" href="#/" on-click="['formula_charge',q,r,'7+']">7+</a>
                </div>
            </div>
            [[#options.reaction.phase]]
            <div class="input-group-append">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">[[phaseDefault(phase)]]</button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="#/" on-click="['formula_phase',q,r,'(s)']">solid</a>
                    <a class="dropdown-item" href="#/" on-click="['formula_phase',q,r,'(l)']">liquid</a>
                    <a class="dropdown-item" href="#/" on-click="['formula_phase',q,r,'(g)']">gas</a>
                    <a class="dropdown-item" href="#/" on-click="['formula_phase',q,r,'(aq)']">aqueous</a>
                    <a class="dropdown-item" href="#/" on-click="['formula_phase',q,r,null]">none</a>
                </div>
            </div>
            [[/options.reaction.phase]]
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" on-click="['removeSpecies',q]" id="question_[[q]]_choice_[[c]]_remove" type="button"><i class="far fa-trash-alt"></i></button>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">[[formulaType]]</button>
        <div class="dropdown-menu">
            <a class="dropdown-item" href="#/" on-click="['formula_type','Exact']">Exact</a>
            <!--<a class="dropdown-item" href="#/" on-click="['formula_type','Molecular']">Molecular</a>-->
        </div>
    </div>
</div>
[[/partial]]

        [[#partial variables]]
        <!-- Variable types:
        0: Number
        1: Array
        2: String
        3: Computed
        4: Selection
        5: Chemical Formula
        -->
        <table class='table'>
            <thead>
            <tr>
                <th>Name</th>
                <th>Title</th>
                <th>Type</th>
                <th>Description</th>
                <th>Remove</th>
                <th>Move</th>
            </tr>
            </thead>
            <tbody>
            [[#each variables]]
            <tr>
                <td>
                    <textarea type="text" rows="1" id="q_[[q]]_var_[[@index]]" class="form-control" value="[[name]]"></textarea>
                </td>
                <td>
                    [[#type == 3]]
                    <button id="computed" type="button" class="btn btn-outline-dark btn-sm mb-2" data-toggle="modal" data-target="#computed_help">
                        Help
                    </button>
                    [[else]]
                    <textarea type="text" rows="1" class="form-control" value="[[title]]" ></textarea>
                    [[/type]]
                </td>
                <td>
                    <select id="course_select" name="course_id" value='[[type]]' >
                        <option value="0">Number</option>
                        <option value="1">Array</option>
                        <option value="2">String</option>
                        <option value="3">Computed</option>
                        <option value="4">Selection</option>
                        <option value="5">Chemical Formula</option>
                    </select>
                </td>
                <td style='width: 40%'>
                    [[#type == 4]]
                    <div class="row">
                        <div class="col-auto">

                            <button class="btn btn-sm btn-secondary" on-click="editSelectionVariable" data-toggle="modal" data-target="#selection_variable_modal">Edit Choices ([[!choices ? '0' : choices.length]])</button>

                        </div>
                        <div class="col"><textarea type="text" rows="1" class="form-control" value="[[descript]]" ></textarea></div>
                    </div>
                    [[else]]
                    <div class="col"><textarea type="text" rows="1" class="form-control" value="[[descript]]" ></textarea></div>
                    [[/type]]
                </td>
                <td>
                    <button class='btn btn-sm btn-warning' on-click="deleteVariable">Delete</button>
                </td>
                <td style='width: 10%'>
                    <button class="btn btn-sm inline" id= "up" on-click="moveVariable"><i class="fas fa-long-arrow-alt-up"></i></button>
                    <button class="btn btn-sm inline" id= "down" on-click="moveVariable"><i class="fas fa-long-arrow-alt-down"></i></button>
                </td>
            </tr>
            [[/each]]
            </tbody>
        </table>
        [[/partial]]

        [[#partial inter_variables]]
        <table class='table'>
            <tr>
                <th>Name</th>
                <th></th>
                <th>Computation</th>
                <th>Remove</th>
                <th>Move</th>
            </tr>

            [[#each inter_variables]]
            <tr>
                <td style='width: 20%'>
                    <textarea type="text" rows="1" id="[[html_id]]" class="form-control" value="[[name]]" ></textarea>
                </td>
                <td>
                    =
                </td>
                <td style='width: 70%'>
                    <textarea type="text" rows="1" class="form-control" value="[[equation]]" ></textarea>
                </td>
                <td>
                    <button class='btn btn-sm btn-warning' on-click='deleteIter'>Delete</button>
                </td>
                <td style='width: 20%'>
                    <button class='btn btn-sm inline' id="Up" on-click='moveInter'><i class="fas fa-long-arrow-alt-up"></i></button>
                    <button class='btn btn-sm inline' id="Down" on-click='moveInter'><i class="fas fa-long-arrow-alt-down"></i></button>
                </td>
            </tr>
            [[/each]]
        </table>

        [[/partial]]

        [[#partial conditions]]

        <table class='table'>
            <tr>
                <th>Condition</th>
                <th>Return type</th>
                <th>Expression to Return</th>
                <th>Points</th>
                <th>Remove</th>
                <th>Move</th>
            </tr>
            [[#each conditions:c]]
            <tr>
                [[#if @key < conditions.length - 1]]
                <td style='width: 30%'>
                    <textarea type="text" id="[[html_id]]" class="form-control"  value="[[equation]]"></textarea>
                </td>

                <td>
                    <select id="course_select" name="course_id" value='[[type]]'  style="background-color:[[type == 1 ? 'limegreen' : 'red']];">
                        <option value="1">Return if true</option>
                        <option value="0">Return if false</option>
                    </select>
                </td>
                [[else]]
                <td style='width: 40%' colspan="2">
                    Default case
                </td>
                [[/if]]
                <td style='width: 25%'>
                    <textarea type="text" class="form-control"  value="[[result]]"></textarea>
                </td>
                <td style='width: 10%'>
                    <input type='number' class='form-control'  value="[[points]]" />
                </td>
                <td style='width: 5%'>
                    <button class='btn btn-sm btn-warning' on-click="deleteCondition">Delete</button>
                </td>
                <td style='width:10%'>
                    [[#if @key < conditions.length - 1]]
                    <button class='btn btn-sm inline' id="Up" on-click='moveCondition'><i class="fas fa-long-arrow-alt-up"></i></button>
                    [[#if @key < conditions.length - 2]]
                    <button class='btn btn-sm inline' id="Down" on-click='moveCondition'><i class="fas fa-long-arrow-alt-down"></i></button>
                    [[/if]]
                    [[/if]]
                </td>
            </tr>
            [[/each]]
        </table>

        [[/partial]]
    </div>
</div>

<div class="footer px-5 d-none d-md-block">
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <div class="btn-group dropup">
                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="jumpToDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Jump to...
                    </button>
                    <div class="dropdown-menu" aria-labelledby="jumpToDropdown">
                        [[#questions:q]]
                            <a class="dropdown-item" href="#q_[[q]]_focus">[[name]]</a>
                        [[/questions]]
                    </div>
                </div>
                <a class="btn btn-sm btn-secondary" href="#"><i class="fas fa-long-arrow-alt-up"></i></a>
                <a class="btn btn-sm btn-secondary mr-2" href="#responseMsgs"><i class="fas fa-long-arrow-alt-down"></i></a>
                <button class="btn btn-sm btn-light" on-click="collapseAll"><i class="fas fa-compress"></i></button>
                <button class="btn btn-sm btn-light mr-2" on-click="expandAll"><i class="fas fa-expand-arrows-alt"></i></button>
                <button class="btn btn-sm btn-light mr-2" on-click="reorderQuestions" data-toggle="modal" data-target="#question_order_modal"><i class="fas fa-sort"></i></button>

                <div class="btn-group">
                    <button on-click="addQuestion" class="btn btn-sm btn-success"><i class="fas fa-question"></i></button>
                    <button on-click="addSimple" class="btn btn-sm btn-success"><i class="fas fa-list-ol"></i></button>
                    <button on-click="addSimpleText" class="btn btn-sm btn-success"><i class="fas fa-font"></i></button>
                    <button on-click="addShort" class="btn btn-sm btn-success"><i class="fas fa-pen"></i></button>
                    <button on-click="addUnanswered" class="btn btn-sm btn-success"><i class="fas fa-info-circle"></i></button>
                    <button on-click="addMolecule" class="btn btn-sm btn-success"><i class="fas fa-dna"></i></button>
                    <button on-click="addMultipleChoice" class="btn btn-sm btn-success"><i class="fas fa-list"></i></button>
                    <button on-click="addReaction" class="btn btn-sm btn-success"><i class="fas fa-long-arrow-alt-right"></i></button>
                    <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#paste_JSON_modal"><i class="fas fa-paste"></i></button>
                </div>

            </div>
            <div class="col-md-4 text-right">
                [[#if editing]]
                <button type="button" class='btn btn-info btn-sm' on-click="save">Update Assignment</button>
                [[else]]
                <button type="button" class='btn btn-info btn-sm' on-click="save">Save Assignment</button>
                [[/if]]
            </div>
        </div>
    </div>
</div>


<style>

    td
    {
        padding: 4px;
    }

    .centered
    {
        text-align: center;
    }

    .left-space
    {
        margin-left: 10px;
    }

    .inline
    {
        display: inline-block;
    }

    body {
        margin-bottom: 50px; /* Margin bottom by footer height */
    }

    .footer {
        position: fixed;
        bottom: 0;
        width: 100%;
        height: 50px; /* Set the fixed height of the footer here */
        line-height: 50px; /* Vertically center the text there */
        background-color: #c3ccd4;

    }

</style>

@include('instructor.assignments.editorModals')
