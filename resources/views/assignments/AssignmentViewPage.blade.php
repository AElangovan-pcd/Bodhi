<div class="container">
    [[#student_view]]
    @if(isset($user))
    <div class="alert [[submit_for_student ? 'alert-danger' : 'alert-info']]" role="alert">
        [[#submit_for_student]]
        <p>Viewing and modifying answers for {{$user->firstname}} {{$user->lastname}}.</p>
        <p><strong>You will change submissions/scores by evaluating.</strong>  If you want to view only without modifying the student's assignment, click the button.</p>
        <button class="btn btn-sm btn-outline-dark" on-click="@.toggle('submit_for_student')">View Student Results</button>
        [[else]]
        <p>Viewing answers for {{$user->firstname}} {{$user->lastname}}.</p>
        <p>In this view, you can see the assignment as a student, but you will not affect their submissions/scores by evaluating.  If you want to submit for the student, click the button to set this mode.</p>
        <button class="btn btn-sm btn-outline-danger" on-click="@.toggle('submit_for_student')">Submit for Student</button>
        [[/submit_for_student]]
    </div>
    @endif
    [[/student_view]]
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
    <h2>[[assignment.name]]</h2>
    <p>[[[assignment.description]]]</p>
    [[#assignment.type != 2]]
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link active" href="#">Assignment</a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="share">Shared Answers</a>
        </li>
    </ul>
    [[#assignment.options.gradedFlagFilter]]
    <button class="btn btn-sm btn-info mt-2" on-click="gradedFlagFilter">Show [[gradedFlagFilter ? 'All Questions' : 'Graded Questions Only']]</button>
    [[/assignment.options.gradedFlagFilter]]
    [[/assignment.type]]
    [[#assignment.type == 2 && quiz_controls]]
    [[>quiz_timing]]
    [[/assignment.type]]

    [[#questions:q]]
    [[#if !~/gradedFlagFilter || options.gradedFlag]]
    <div class="card mt-3">
        <div class="card-header d-flex"><span class="mr-auto">
                [[#if ~/assignment.options.questionAutoNumbering && questionNumbers[q] > 0]] [[questionNumbers[q] ]]) [[/if]]
                [[~/assignment.options.suppressQuestionNames ? '' : name]]
                [[#options.gradedFlag]]
                <div class="badge badge-pill badge-info">Graded Question</div>
                [[/options.gradedFlag]]
            </span><span class="ml-auto">
                [[#type != UNANSWERED_QUESTION]]
                <!--Attempts-->
                [[#~/assignment.options.showAttempts]]
                [[#result.earned != undefined]]
                <div class="badge badge-pill">[[result.attempts]] Attempt[[result.attempts != 1 ? 's' : '']]</div>
                [[/result.earned]]
                [[/~/assignment.options.showAttempts]]
                <!--End Attempts-->
                <!--Completion vs points-->
                [[#options.completionDisplay]]
                [[#result.earned == undefined]]

                [[elseif (result.earned == 0 && max_points != 0)]]
                <div class="badge badge-pill badge-secondary">Incomplete</div>
                [[elseif (result.earned/max_points != 1) && max_points != 0]]
                <div class="badge badge-pill badge-warning">Partially Complete</div>
                [[else]]
                <div class="badge badge-pill badge-success">Complete!</div>
                [[/result.earned]]
                [[else]]
                [[result.earned != undefined ? result.earned : '-']]/[[max_points]] points
                [[/options.completionDisplay]]
                <!--End completion vs points-->
                [[/type]]
            </span>
        </div>
        <div class="card-body">
            [[[description]]]

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

            [[#if intermediates]]
            <div class="alert alert-warning mt-2 mb-0">
                <table class="table" style="margin-bottom:0">
                    <tbody>
                [[#intermediates:i]]
                    <tr><td>[[@key]]</td><td>[[intermediates[i]]]</td></tr>
                [[/intermediates]]
                    </tbody>
                </table>
            </div>
            [[/if]]

            [[#if result != "" && result != null]]
            [[#if type == 2]] <!--Graded Written Question-->
                [[#if result.status == 0 || result.status == 3]] <!--Ungraded (0) or deferred feedback (3) -->
                    <div class="alert alert-secondary mt-2 mb-0" id="result_[[q]]">[[result.feedback ? result.feedback : "Answer submitted. Waiting on graded response."]]</div>
                [[elseif result.status == 1]] <!--Graded-->
                    <div class="alert alert-info mt-2 mb-0" id="result_[[q]]">[[result.feedback]]</div>
                [[elseif result.status == 2]] <!--Retry-->
                    <div class="alert alert-secondary mt-2 mb-0" id="result_[[q]]">Update and resubmit your response.<br/><br/>[[result.feedback]]</div>
                [[elseif result.status == 4]] <!--Resubmit-->
                <div class="alert alert-secondary mt-2 mb-0" id="result_[[q]]">Update your response and submit. To revert to your previously submitted response, reload the page.</div>
                [[else]]  <!--Error-->
                <div class="alert alert-warning mt-2 mb-0" id="result_[[q]]">[[result.feedback]]</div>
                [[/if]]
            [[else]]
            <div class="alert [[result.error == true ? 'alert-warning' : result.earned == max_points ? 'alert-success' : 'alert-info']] mt-2 mb-0" id="result_[[q]]">[[result.feedback]]</div>
            [[/if]]
            [[/if]]
        </div>
        [[#extra.view]]
        <div class="card-footer">
            [[[extra.text]]]
        </div>
        [[/extra.view]]
        [[#~/assignment.options.inline_discussion == true]]
            [[#discuss]]
            <div class="card-footer">
                @if($instructor==1)
                    <button class="btn-sm btn-outline-dark mt-1" on-click="showNames">[[showNames == true ? 'Hide Names' : 'Show Names']]</button>
                @endif
                <table class="table table-striped mt-2">
                    <tbody>
                    [[#comments:c]]
                    <tr>
                        <td>[[[contents]]]</td>
                        @if($instructor==1)
                            [[#../../showNames]]<td>([[user.firstname]] [[user.lastname]][[user.firstname==null ? 'Reload to see name ' : '']])</td>[[/../../showNames]]
                            <td class="text-right">
                                <button class="btn btn-sm btn-danger"  id="[[id]]" data-toggle="modal" data-target="#modal_delete_comment_[[id]]"><i class="far fa-trash-alt" aria-hidden></i><span class="sr-only">Delete</span></button>
                            </td>
                        @endif
                    </tr>
                    [[/comments]]
                    </tbody>
                </table>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            Comment&nbsp
                            <i class="far fa-question-circle" aria-hidden data-toggle="tooltip" data-placement="right" title="You will be anonymous to your classmates, but your instructor will know your identity."></i>
                        </span>
                    </div>
                    <input type="text" class="form-control" id="[[c]]" on-keydown="submitComment" placeholder="Add a comment to the discussion for this question." value="[[newComment]]">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary [[newComment == null || newComment == '' ? 'disabled' : '']]" on-click="submitComment" id=[[q]] type="button">[[submitting == true ? 'Submitting...' : 'Submit']]</button>
                    </div>
                </div>
            </div>
            [[/discuss]]
        [[/assignment.options.inline_discussion]]
    </div>
    [[#comments:c]]
    <!-- Modal to confirm the removal of a comment-->
    <div class="modal fade" id="modal_delete_comment_[[id]]">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Warning</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p>This action cannot be undone.</p>
                    <p>Are you sure you want to delete this comment?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-dark" data-dismiss="modal">Cancel</button>
                    <a href="deleteComment/[[id]]" class="btn btn-danger">Delete</a>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    [[/comments]]
    [[/if]] <!--Graded flag filter-->
    [[/questions]]

    [[#questions.length > 0]]
    [[#unAnsweredCount > 0]]
    <div class="alert alert-danger mt-2">
        You have <strong>[[unAnsweredCount]]</strong> question[[unAnsweredCount>1 ? 's' : '']] that [[unAnsweredCount>1 ? 'have' : 'has']] not been submitted. Please note that you must submit each question individually.
    </div>
    [[else]]
    <div class="alert alert-success mt-2">
        All questions have been submitted on this page.  You can verify that your answers have been recorded as expected by refreshing the page.
    </div>
    [[/unAnsweredCount]]
    [[/questions.length]]

    [[#assignment.type == 2]]
        [[>quiz_controls]]
    [[/assignment.type]]
</div>

[[#partial stdQuestion]]
<div class="d-block">
    [[#variables:v]]
        [[#type == 0 || type == 2]] <!--Numeric or string-->
            <div class="d-block">
                <label for='[[name]]'> <strong>[[[title]]]</strong> </label>
                <input type='text' id='[[name]]' class="input form-control standard_input [[type == 2 ? 'col-sm-4' : '']]" name='[[name]]'
                    value='[[answer.submission]]' [[type==0 ? 'style="width: 120px;"' : '']] title="[[title]]" />
                <p class="form-text" style="color: #737373">[[[descript]]]</p>
            </div>
        [[elseif type == 1]] <!--Array-->
            <div class="d-inline-block">
                <label for='[[name]]'> <strong>[[[title]]]</strong> </label>
                <div id='desc_[[name]]' name="[[name]]" style="width: 10em; hyphens:auto; color: #737373"> [[[descript]]] </div>
                <textarea rows="5" id="[[name]]" name="[[id]]" class='input form-control array_input'
                          style='width: 7em; resize:both'>[[answer.submission]]</textarea>
                <br>
            </div>
        [[elseif type == 4]] <!--Selection-->
            <div class="d-block">
                <label for='[[name]]'> <strong>[[[title]]]</strong> </label>

                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton_[[q]]_[[v]]" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        [[choiceName(q,v,answer.submission)]]
                    </button>

                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        [[#choices:c]]
                        <a class="dropdown-item" href="#/" on-click="['selectVariableChoice',q,v]">[[name]]</a>
                        [[/choices]]
                    </div>

                </div>
                <p class="form-text" style="color: #737373">[[[descript]]]</p>
            </div>
        [[elseif type == 5]] <!--Chemical Formula-->
            <div class="d-block">
                <label for='q_[[q]]_v_[[v]]'> <strong>[[[title]]]</strong> </label>
                <div class="row">
                    <div class="col-md-6">
                        <div class="input-group input-group-sm mb-2" id="q_[[q]]_v_[[v]]">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="q_[[q]]_v_[[v]]_formula">Formula</span>
                            </div>
                            <input type="text" class="form-control" on-keyup="['formula_preview',q,v]" aria-label="Formula" aria-describedby="q_[[q]]_v_[[v]]_formula" value='[[answer.submission.formula]]'>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">[[chargeDefault(answer.submission.charge)]]</button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="#/" on-click="['formula_charge',q,v,'7-']">7-</a>
                                    <a class="dropdown-item" href="#/" on-click="['formula_charge',q,v,'6-']">6-</a>
                                    <a class="dropdown-item" href="#/" on-click="['formula_charge',q,v,'5-']">5-</a>
                                    <a class="dropdown-item" href="#/" on-click="['formula_charge',q,v,'4-']">4-</a>
                                    <a class="dropdown-item" href="#/" on-click="['formula_charge',q,v,'3-']">3-</a>
                                    <a class="dropdown-item" href="#/" on-click="['formula_charge',q,v,'2-']">2-</a>
                                    <a class="dropdown-item" href="#/" on-click="['formula_charge',q,v,'-']">-</a>
                                    <a class="dropdown-item" href="#/" on-click="['formula_charge',q,v,null]">0</a>
                                    <a class="dropdown-item" href="#/" on-click="['formula_charge',q,v,'+']">+</a>
                                    <a class="dropdown-item" href="#/" on-click="['formula_charge',q,v,'2+']">2+</a>
                                    <a class="dropdown-item" href="#/" on-click="['formula_charge',q,v,'3+']">3+</a>
                                    <a class="dropdown-item" href="#/" on-click="['formula_charge',q,v,'4+']">4+</a>
                                    <a class="dropdown-item" href="#/" on-click="['formula_charge',q,v,'5+']">5+</a>
                                    <a class="dropdown-item" href="#/" on-click="['formula_charge',q,v,'6+']">6+</a>
                                    <a class="dropdown-item" href="#/" on-click="['formula_charge',q,v,'7+']">7+</a>
                                </div>
                            </div>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">[[phaseDefault(answer.submission.phase)]]</button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="#/" on-click="['formula_phase',q,v,'(s)']">solid</a>
                                    <a class="dropdown-item" href="#/" on-click="['formula_phase',q,v,'(l)']">liquid</a>
                                    <a class="dropdown-item" href="#/" on-click="['formula_phase',q,v,'(g)']">gas</a>
                                    <a class="dropdown-item" href="#/" on-click="['formula_phase',q,v,'(aq)']">aqueous</a>
                                    <a class="dropdown-item" href="#/" on-click="['formula_phase',q,v,null]">none</a>
                                </div>
                            </div>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" data-toggle="modal" data-target="#formula_help_modal" aria-label="Help with formula submissions"><i class="fas fa-question-circle" aria-hidden="true"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <span class="mr-4">Preview: </span>
                        <span id="q_[[q]]_v_[[v]]_formula_preview">
                        </span>
                    </div>
                </div>
                <p class="form-text" style="color: #737373">[[[descript]]]</p>
            </div>
        [[/type]]
    [[/variables]]
</div>
    <div class="d-block"></div>
    @if($instructor==1)
        [[#~/submit_for_student]]
        <div class="alert alert-danger">You are submitting for {{$user->firstname." ".$user->lastname}}. This will modify their assignment.</div>
        [[/~/submit_for_student]]
    @endif
    @if($instructor==1)
        <div class="btn-group" role="group" aria-label="Basic example">
            <button type="button" class="btn btn-primary" on-click="evalQuestion" id="[[id]]" name="[[q]]">Evaluate</button>
            <button type="button" class="btn btn-info" on-click="['evalQuestion','true']" id="[[id]]" name="[[q]]">Show Intermediates</button>
        </div>
    @else
        <button type="button" class="btn btn-primary" on-click="evalQuestion" id="[[id]]" name="[[q]]">Evaluate</button>
    @endif
    [[#if computed && extra.newValues]]
        <a href="newValues/[[id]]" class="btn btn-outline-danger">Change Problem Values</a>
    [[/if]]
    [[#~/assignment.options.inline_discussion == true]]
    <button type="button" class="btn btn btn-outline-primary" on-click="discuss" id="q">[[discuss != true ? 'Discuss' : 'Close Discussion']] <span class="badge badge-primary">[[comments.length > 0 ? comments.length : '0']]</span></button>
    [[/assignment.options.inline_discussion]]
    [[#extra.available==1]]
    <button class="btn btn-outline-secondary" on-click="extra" id="[[q]]">Extra Info</button>
    [[/extra.available]]
[[/partial]]

[[#partial simpleQuestion]]
<div class="d-block mt-2">
    <input type='text' id='[[name]]' class='input form-control standard_input' name='[[name]]'
           value='[[answer.submission]]' style='width: 120px' title="[[title]]" />
    <p class="form-text" style="color: #737373">[[[descript]]]</p>
</div>
<div class="d-block"></div>

@if($instructor==1)
    [[#~/submit_for_student]]
    <div class="alert alert-danger">You are submitting for {{$user->firstname." ".$user->lastname}}. This will modify their assignment.</div>
    [[/~/submit_for_student]]
@endif
<div class="btn-group" role="group" aria-label="Basic example">
    <button type="button" class="btn btn-primary" on-click="evalQuestion" id="[[id]]" name="[[q]]">Evaluate</button>
</div>

[[#~/assignment.options.inline_discussion == true]]
<button type="button" class="btn btn btn-outline-primary" on-click="discuss" id="q">[[discuss != true ? 'Discuss' : 'Close Discussion']] <span class="badge badge-primary">[[comments.length > 0 ? comments.length : '0']]</span></button>
[[/assignment.options.inline_discussion]]
[[#extra.available==1]]
<button class="btn btn-outline-secondary" on-click="extra" id="[[q]]">Extra Info</button>
[[/extra.available]]
[[/partial]]

[[#partial simpleTextQuestion]]
<div class="d-block mt-2">
    <input type='text' id='[[name]]' class='input form-control standard_input col-sm-4' name='[[name]]'
           value='[[answer.submission]]' title="[[title]]" />
    <p class="form-text" style="color: #737373">[[[descript]]]</p>
</div>
<div class="d-block"></div>

@if($instructor==1)
    [[#~/submit_for_student]]
    <div class="alert alert-danger">You are submitting for {{$user->firstname." ".$user->lastname}}. This will modify their assignment.</div>
    [[/~/submit_for_student]]
@endif
<div class="btn-group" role="group" aria-label="Basic example">
    <button type="button" class="btn btn-primary" on-click="evalQuestion" id="[[id]]" name="[[q]]">Evaluate</button>
</div>

[[#~/assignment.options.inline_discussion == true]]
<button type="button" class="btn btn btn-outline-primary" on-click="discuss" id="q">[[discuss != true ? 'Discuss' : 'Close Discussion']] <span class="badge badge-primary">[[comments.length > 0 ? comments.length : '0']]</span></button>
[[/assignment.options.inline_discussion]]
[[#extra.available==1]]
<button class="btn btn-outline-secondary" on-click="extra" id="[[q]]">Extra Info</button>
[[/extra.available]]
[[/partial]]

[[#partial moleculeQuestion]]
<div class="d-block mt-2">
    <div id="moleculeEditor_[[@index]]"></div>
</div>
<div class="d-block"></div>
@if($instructor==1)
    [[#~/submit_for_student]]
    <div class="alert alert-danger">You are submitting for {{$user->firstname." ".$user->lastname}}. This will modify their assignment.</div>
    [[/~/submit_for_student]]
@endif
<button type="button" class="btn btn-primary" on-click="evalMoleculeQuestion" id="[[id]]" name="[[q]]">Evaluate</button>
[[#~/assignment.options.inline_discussion == true]]
<button type="button" class="btn btn btn-outline-primary" on-click="discuss" id="q">[[discuss != true ? 'Discuss' : 'Close Discussion']] <span class="badge badge-primary">[[comments.length > 0 ? comments.length : '0']]</span></button>
[[/assignment.options.inline_discussion]]
[[#extra.available==1]]
<button class="btn btn-outline-secondary" on-click="extra" id="[[q]]">Extra Info</button>
[[/extra.available]]
[[/partial]]

[[#partial multipleChoiceQuestion]]

<ul class="list-group">
    [[#choices:c]]
    <li class="list-group-item">
        <div class="form-check">
            <input class="form-check-input" type="[[../../options.MC.type == 'single' ? 'radio' : 'checkbox']]" value="[[id]]" name="question_[[q]]_choices" id="question_[[q]]_choice_[[c]]" on-click="['selectMCChoice',id, q, c]" [[answer.submission.toString().split(',').includes(id.toString()) === true ? 'checked' : '']]>
            <label class="form-check-label" for="question_[[q]]_choice_[[c]]">
                [[[description]]]
            </label>
        </div>
    </li>

    [[/choices]]
</ul>

@if($instructor==1)
    [[#~/submit_for_student]]
    <div class="alert alert-danger">You are submitting for {{$user->firstname." ".$user->lastname}}. This will modify their assignment.</div>
    [[/~/submit_for_student]]
@endif
<div class="btn-group" role="group" aria-label="Basic example">
    <button type="button" class="btn btn-primary mt-2" on-click="evalQuestion" id="[[id]]" name="[[q]]">Evaluate</button>
</div>

[[#~/assignment.options.inline_discussion == true]]
<button type="button" class="btn btn btn-outline-primary" on-click="discuss" id="q">[[discuss != true ? 'Discuss' : 'Close Discussion']] <span class="badge badge-primary">[[comments.length > 0 ? comments.length : '0']]</span></button>
[[/assignment.options.inline_discussion]]
[[#extra.available==1]]
<button class="btn btn-outline-secondary" on-click="extra" id="[[q]]">Extra Info</button>
[[/extra.available]]
[[/partial]]

[[#partial shortQuestion]]
<div class='row tex2jax_ignore'>
    <div class='col-md-12'>
        <div id="written_answer_[[id]]">[[[value]]]</div>
    </div>
</div>
<h5 hidden id='response_text_[[id]]'>Professor's Response</h5>
<div hidden class='card bg-light' id='prof_response_[[id]]'></div> <br>
[[#if !result || result.status == 2 || result.status == 4]]
Answer not yet submitted. To submit, click Preview then Submit.<br/>
[[#options.resubmission]]
You may make updates to your submission until it is graded.
[[else]]
You will only be able to submit once for this question.
[[/options.resubmission]]
<br/>
@if($instructor==1)
    [[#~/submit_for_student]]
    <div class="alert alert-danger">You are submitting for {{$user->firstname." ".$user->lastname}}. This will modify their assignment.</div>
    [[/~/submit_for_student]]
@endif
<button on-click='previewWrittenAnswer' id='[[id]]' name=[[q]] class='btn btn-secondary' data-toggle="modal" data-target="#previewModal_[[q]]"> Preview Submission </button>
[[elseif result && options.resubmission && ![1,3].includes(result.status)]]
<button class="btn btn-outline-primary" on-click="changeWrittenSubmission">Change Submission</button>
[[/if]]
[[#extra.available==1]]
<button class="btn btn-outline-secondary" on-click="extra" id="[[q]]">Extra Info</button>
[[/extra.available]]
[[/partial]]

[[#partial unansweredQuestion]]
    [[#~/assignment.options.inline_discussion == true]]
    <div class="d-block"></div>
    <button type="button" class="btn btn btn-outline-primary" on-click="discuss" id="q">[[discuss != true ? 'Discuss' : 'Close Discussion']] <span class="badge badge-primary">[[comments.length > 0 ? comments.length : '0']]</span></button>
    [[/assignment.options.inline_discussion]]
    [[#extra.available==1]]
    <button class="btn btn-outline-secondary" on-click="extra" id="[[q]]">Extra Info</button>
    [[/extra.available]]
[[/partial]]

[[#partial reactionQuestion]]
<div class="d-block mt-2">
    <div class="mt-2"><strong>Reactants</strong></div>
    [[#answer.submission.reactants:v]]
    [[>formula]]
    [[/answer.submission.reactants]]
    <button class="btn btn-sm btn-success" on-click="addReactionReactant">Add Reactant</button>

    <div class="mt-2"><strong>Products</strong></div>
    [[#answer.submission.products:v]]
    [[>formula]]
    [[/answer.submission.products]]
    <button class="btn btn-sm btn-success" on-click="addReactionProduct">Add Product</button>
</div>
<div class="d-block">
    <br/>
    <strong>Preview: </strong><div id="q_[[q]]_reaction_preview_assignment"></div>
    <br/>
</div>

@if($instructor==1)
    [[#~/submit_for_student]]
    <div class="alert alert-danger">You are submitting for {{$user->firstname." ".$user->lastname}}. This will modify their assignment.</div>
    [[/~/submit_for_student]]
@endif
<div class="btn-group" role="group" aria-label="Basic example">
    <button type="button" class="btn btn-primary" on-click="evalQuestion" id="[[id]]" name="[[q]]">Evaluate</button>
</div>

[[#~/assignment.options.inline_discussion == true]]
<button type="button" class="btn btn btn-outline-primary" on-click="discuss" id="q">[[discuss != true ? 'Discuss' : 'Close Discussion']] <span class="badge badge-primary">[[comments.length > 0 ? comments.length : '0']]</span></button>
[[/assignment.options.inline_discussion]]
[[#extra.available==1]]
<button class="btn btn-outline-secondary" on-click="extra" id="[[q]]">Extra Info</button>
[[/extra.available]]
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
                    <a class="dropdown-item" href="#/" on-click="['reaction_formula_charge',q,r,'7-']">7-</a>
                    <a class="dropdown-item" href="#/" on-click="['reaction_formula_charge',q,r,'6-']">6-</a>
                    <a class="dropdown-item" href="#/" on-click="['reaction_formula_charge',q,r,'5-']">5-</a>
                    <a class="dropdown-item" href="#/" on-click="['reaction_formula_charge',q,r,'4-']">4-</a>
                    <a class="dropdown-item" href="#/" on-click="['reaction_formula_charge',q,r,'3-']">3-</a>
                    <a class="dropdown-item" href="#/" on-click="['reaction_formula_charge',q,r,'2-']">2-</a>
                    <a class="dropdown-item" href="#/" on-click="['reaction_formula_charge',q,r,'-']">-</a>
                    <a class="dropdown-item" href="#/" on-click="['reaction_formula_charge',q,r,null]">0</a>
                    <a class="dropdown-item" href="#/" on-click="['reaction_formula_charge',q,r,'+']">+</a>
                    <a class="dropdown-item" href="#/" on-click="['reaction_formula_charge',q,r,'2+']">2+</a>
                    <a class="dropdown-item" href="#/" on-click="['reaction_formula_charge',q,r,'3+']">3+</a>
                    <a class="dropdown-item" href="#/" on-click="['reaction_formula_charge',q,r,'4+']">4+</a>
                    <a class="dropdown-item" href="#/" on-click="['reaction_formula_charge',q,r,'5+']">5+</a>
                    <a class="dropdown-item" href="#/" on-click="['reaction_formula_charge',q,r,'6+']">6+</a>
                    <a class="dropdown-item" href="#/" on-click="['reaction_formula_charge',q,r,'7+']">7+</a>
                </div>
            </div>
            [[#options.reaction.phase]]
            <div class="input-group-append">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">[[phaseDefault(phase)]]</button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="#/" on-click="['reaction_formula_phase',q,r,'(s)']">solid</a>
                    <a class="dropdown-item" href="#/" on-click="['reaction_formula_phase',q,r,'(l)']">liquid</a>
                    <a class="dropdown-item" href="#/" on-click="['reaction_formula_phase',q,r,'(g)']">gas</a>
                    <a class="dropdown-item" href="#/" on-click="['reaction_formula_phase',q,r,'(aq)']">aqueous</a>
                    <a class="dropdown-item" href="#/" on-click="['reaction_formula_phase',q,r,null]">none</a>
                </div>
            </div>
            [[/options.reaction.phase]]
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" on-click="['removeSpecies',q]" id="question_[[q]]_choice_[[c]]_remove" type="button"><i class="far fa-trash-alt"></i></button>
            </div>
        </div>
    </div>
</div>
[[/partial]]

<!-- Modal for previewing written answer -->
[[#questions:q]]
<div class="modal fade" id="previewModal_[[q]]" tabindex="-1" role="dialog" aria-labelledby="previewModalLabel_[[q]]">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel-[[q]]">Preview Written Submission</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                [[&~/submissionPreview]]
            </div>
            <div class="modal-footer">
                <button on-click='submitWrittenAnswer' id='[[id]]' name=[[q]] class='btn btn-primary'> Submit Answer </button>
            </div>
        </div>
    </div>
</div>
[[/questions]]

[[#partial quiz_timing]]
<div class="card mt-2">
    <div class="card-header">Instructions</div>
    <div class="card-body">[[[ quiz_controls.instructions]]]</div>
</div>
<div class="card mt-2">
    <div class="card-header">Timing</div>
    <div class="card-body">
        <div class="table table-hover">
            <tbody>
            <tr>
                <td>Allowed Start</td><td>[[quiz_controls.allowed_start]]</td>
            </tr>
            <tr>
                <td>Assessment Closes</td><td>[[quiz_controls.allowed_end]]</td>
            </tr>
            <tr>
                <td>Time Allowed Once Started</td><td>[[quiz_controls.allowed_minutes]] minutes</td>
            </tr>
            [[#quiz_controls.status > 0]]
            <tr>
                <td>Actual time started</td><td>[[quiz_controls.actual_start]]</td>
            </tr>
            [[/quiz_controls.status]]
            </tbody>
        </div>
        <p>Current LabPal Time at Page Load: [[quiz_controls.loaded_time]]</p>
        <div class="alert [[quiz_controls.allow.allow ? 'alert-success' : 'alert-warning']]">[[quiz_controls ? quiz_controls.allow.message : 'You have not yet been assigned an assessment.']]</div>
        [[#if quiz_controls.status === 1 && quiz_controls.allow.allow === true]]
        <p>You are on page [[pages.current]] of [[pages.total]].</p>
        [[/if]]
    </div>
</div>
[[/partial]]

[[#partial quiz_controls]]
<div class="card mt-2">
    <div class="card-body">
        [[#!quiz_controls]]
        <div class="alert alert-warning">You have not yet been assigned an assessment.</div>
        [[/quiz_controls]]
        [[#if quiz_controls.status === 0 && quiz_controls.allow.allow === true]]
        <strong>Once you start the assessment, you will be limited to [[quiz_controls.allowed_minutes]] minutes.</strong>
        <br/><br/><a href="quiz_next" class="btn btn btn-info">Start Quiz</a>
        [[elseif quiz_controls.status === 1 && quiz_controls.allow.allow === true]]
        <p>You are on page [[pages.current]] of [[pages.total]].</p>
        [[#if pages.current === pages.total]]
        <strong>Once you click the button to continue to finish the assessment, you will not be able to return.</strong>
        <br/><br/><a href="quiz_next" class="btn btn btn-info">Finish Assessment</a>
        [[else]]
        <strong>Once you click the button to continue to the next page, you will not be able to return to this page of the assessment.</strong>
        <br/><br/><a href="quiz_next" class="btn btn btn-info">Next Page</a>
        [[/if]]
        [[/if]]
    </div>
</div>
[[/partial]]

<!-- Modal for chemical formula help -->
<div class="modal fade" id="formula_help_modal" tabindex="-1" role="dialog" aria-labelledby="formula_help_modal_label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formula_help_modal_label">Help with Chemical Formulas</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>To enter a chemical formula, type the empirical formula with no spaces in the formula box.  Add the charge and the phase, if required, using the Dropdowns.
                    You should see a preview of the formula appear as you type. If you don't see a preview, make sure you are using an up-to-date browser (e.g. Google Chrome or Firefox).</p>
                <p>For example, if you type PO4 in the box, then select "3-" from the charge dropdown and "(aq)" from the phase dropdown, you should see $PO_4^{3-}$.</p>
                <p><strong>Do not attempt to put the charge or the phase in the formula box.</strong></p>
            </div>

        </div>
    </div>
</div>
