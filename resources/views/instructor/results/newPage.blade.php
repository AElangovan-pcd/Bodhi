<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {!! session('status') !!}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-warning" role="alert">
                    {!! session('error') !!}
                </div>
            @endif

            <h2>[[assignment.name]]</h2>
                [[#assignment.linked_assignments_count>0]]
                <div class="alert alert-info text-center">
                    <p><i class="fas fa-link"></i> This is a linked assignment with [[assignment.linked_assignments_count]] children.</p>
                    [[#linked]]
                    <p>You are viewing results for all linked assignments. <a class="btn btn-sm btn-outline-dark" href="main">View Parent Only</a></p>
                    <p>Note that some features aimed at individual students may not yet work in the linked results.  For example, the student view button (eyeball) may not point you to the linked assignment correctly. Visit the child course to use these features for now.</p>
                    [[else]]
                    <p>You are viewing results for this course only. <a class="btn btn-sm btn-outline-dark" href="linked">View Linked Results</a></p>
                    [[/linked]]
                </div>
                [[/assignment.linked_assignments_count]]

            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link [[selected_view == 'points' ? 'active' : '']]" href="#" on-click="@.set('selected_view','points')">Points</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link [[selected_view == 'classroom' ? 'active' : '']]" href="#" on-click="select_classroom">Classroom</a>
                </li>
                [[#!linked]]
                <li class="nav-item">
                    <a class="nav-link [[selected_view == 'values' ? 'active' : '']]" href="#" on-click="@.set('selected_view','values')">Values</a>
                </li>
                [[/linked]]
                [[#writtenQuestionCount > 0]]
                <li class="nav-item">
                    <a class="nav-link [[selected_view == 'written' ? 'active' : '']]" href="#" on-click="select_written">Written Answers [[#if unGraded[writtenQuestionCount] > 0]]<span class="badge badge-pill badge-info">[[unGraded[writtenQuestionCount]]]</span>[[/if]]</a>
                </li>
                [[/writtenQuestionCount]]
                [[#assignment.type == 2]]
                <li class="nav-item">
                    <a class="nav-link [[selected_view == 'quiz' ? 'active' : '']]" href="#" on-click="@.set('selected_view','quiz')" on-click="tooltip">Quiz</a>
                </li>
                [[/assignment.type]]
            </ul>

            [[#selected_view == 'points']]
                [[>points]]
            [[elseif selected_view == 'classroom']]
                [[>classroom]]
            [[elseif selected_view == 'values']]
                [[>values]]
            [[elseif selected_view == 'written']]
                [[>written]]
            [[elseif selected_view == 'quiz']]
                [[>quiz]]
            [[/selected_view]]



        </div>
    </div>
</div>

[[#partial points]]

<button class="btn btn-info btn-sm mt-1 mb-1" on-click="show_attempts">[[show_attempts ? 'Hide' : 'Show']] Attempts</button>
<button class="btn btn-info btn-sm mt-1 mb-1" on-click="toggle_graded_flag">Show [[gradedFlagOnly ? 'All Questions' : 'Graded Questions Only']]</button>
<button type="button" class="btn btn-info btn-sm mt-1 mb-1" on-click="['csv','points']" data-toggle="modal" data-target="#csv_modal">
    Export Scores
</button>
<button type="button" class="btn btn-info btn-sm mt-1 mb-1" on-click="['csv','attempts']" data-toggle="modal" data-target="#csv_modal">
    Export Attempts
</button>
<button type="button" class="btn btn-outline-secondary btn-sm mt-1 mb-1" data-toggle="modal" data-target="#rescore_modal">Rescoring</button>
[[#contains_deferred]]
<button type="button" class="btn btn-outline-secondary btn-sm mt-1 mb-1" data-toggle="modal" data-target="#deferred_modal">Deferred Feedback</button>
[[/contains_deferred]]
[[#assignment.extensions.length > 0]]
<div class="alert alert-warning">This assignment has extensions set for individual students. Students with extensions are able to submit when the assignment is disabled.</div>
[[/assignments.extensions.length]]
<table class="table  table-hover">
    <thead>
    <tr>
        <th>&nbsp</th>
        <th>&nbsp</th>
        <th on-click="['sort','points','first']">First Name</th>
        <th on-click="['sort','points','last']">Last Name</th>
        <th on-click="['sort','points','seat']">Seat</th>
        <th class="text-center" >
            <button class="btn btn-sm outline-secondary" on-click="view_stats" id="[[i]]" type="button" data-toggle="tooltip" data-placement="top" title="View Statistics"><i class="fas fa-chart-bar"></i><span class="sr-only">View Statistics</span></button>
            <br/>
            <span on-click="['sort','points','total']">
            Total <br/>
            ([[assignment_total]]&nbsppossible)
            </span>
        </th>
        [[#each assignment.questions:q]]
        [[#type != 4]]  <!--Exclude info blocks-->
        [[#if ~/gradedFlagOnly != true || options.gradedFlag]]
        <th class="text-center" id="[[q]]" on-click="['sort','points',q]" name="[[this.name]]"><strong>[[this.name]]<br/>([[this.max_points]])</strong></th>
        [[/if]]
        [[/type]]
        [[/each]]

    </tr>
    </thead>
    <tbody>
    [[#each students:row_index]]
    <tr id="row_[[row_index]]">
        <td class="align-middle" ><button class="btn btn-sm" id="[[row_index]]" on-click="show_detail"><i class="far fa-list-alt" aria-hidden style="cursor:pointer;"></i><span class="sr-only">Student Response Detail</span></button></td>
        <td class="align-middle"><a href="student_view/[[id]]"><i class="far fa-eye" style="color:black;" aria-hidden></i><span class="sr-only">Load into Student View</span></a></td>
        <td class="align-middle">
            [[firstname]]
        </td>
        <td class="align-middle">
            [[lastname]]
            [[#extension.updated_at]]<i class="far fa-clock" aria-hidden data-toggle="tooltip" title="Extension allowed. [[extension.expires_at]]"></i><span class="sr-only">Extension allowed.</span>[[/extension.updated_at]]
        </td>
        <td class="align-middle">
            [[pivot.seat]]
        </td>
        <td class="align-middle">
            <div class="progress ">
                <div class="progress-bar [[this.total.fraction == 100 ? 'bg-success' : this.total.fraction < 34 ? 'bg-danger' : this.total.fraction < 67 ? 'bg-warning' : 'bg-info']]" role="progressbar" style="width: [[this.total.fraction]]%" aria-valuenow="[[this.total.earned]]" aria-valuemin="0" aria-valuemax="[[~/assignment_total]]">[[this.total.earned]]</div>
            </div>
        </td>
        [[#each questions:col_index]]
        [[#~/assignment.questions[col_index].type != 4]]
        [[#if ~/gradedFlagOnly != true || (~/assignment.questions[col_index].options != null && ~/assignment.questions[col_index].options.gradedFlag)]]
        <td class="align-middle text-center">
            [[#if this.result == null]]
            <span class="align-middle">-</span> <span class="badge badge-danger align-middle">&nbsp</span>
            [[#~/show_attempts]]<span class="badge align-middle badge-pill badge-dark">0</span>[[/~/show_attempts]]
            [[else]]
            [[#if ~/assignment.questions[@index].type == 2 && this.result.status != 1]]
            <span class="align-middle [[this.result.status==3 ? 'border border-danger' : '']]">[[this.result.earned]]</span> <span class="badge badge-primary align-middle">&nbsp</span>
            [[#~/show_attempts]]<span class="badge align-middle badge-pill [[this.result.attempts < 4 ? 'badge-secondary' : this.result.attempts < 7 ? 'badge-info ' : this.result.attempts < 11 ? 'badge-warning' : 'badge-danger']]">[[this.result.attempts]]</span>[[/~/show_attempts]]
            [[else]]
            <span class="align-middle [[this.result.status==3 ? 'border border-danger' : '']]">[[this.result.earned]]</span>
            <span class="badge align-middle [[this.result.earned == ~/assignment.questions[@index].max_points ? 'badge-success' : this.result.earned > 0 ? 'badge-warning' : 'badge-danger']]">&nbsp</span>
            [[#~/show_attempts]]<span class="badge align-middle badge-pill [[this.result.attempts < 4 ? 'badge-secondary' : this.result.attempts < 7 ? 'badge-info ' : this.result.attempts < 11 ? 'badge-warning' : 'badge-danger']]">[[this.result.attempts]]</span>[[/~/show_attempts]]
            [[/if]]
            [[/if]]
        </td>
        [[/if]]
        [[/~/assignment.questions[col_index].type]]
        [[/each]]

    </tr>
    [[/each]]
    </tbody>
</table>
<span class="border border-danger">Deferred feedback is bordered.</span>
[[/partial]]

<!-- Modal for detail -->
<div id="detail_modal" class="modal fade" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Student Details</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                [[#linked]]
                <div class="alert alert-danger">
                    <strong>Caution!</strong> The buttons on this dialog may not work correctly for linked assignments. It is advised that you make any changes for individual students from the child course rather than from here.
                </div>
                [[/linked]]
                <div class="card-group">
                    <div class="card mb-2">
                        <div class="card-header">Student</div>
                        <div class="card-body">
                            [[detail.student.firstname]] [[detail.student.lastname]]<br/>
                            [[detail.student.email]]<br/>
                            Seat: [[detail.student.pivot.seat]]<br/>
                            Assignment Total: [[detail.student.total.earned]] / [[~/assignment_total]]<br/>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">Extension <i class="far fa-question-circle" aria-hidden data-toggle="tooltip" data-placement="right" title="If the assignment is disabled, users with an extension will still be able to evaluate."></i><span class="sr-only">If the assignment is disabled, users with an extension will still be able to evaluate.</span></div>
                        <div class="card-body">
                            [[#detail.student.extension.updated_at]]
                            [[#detail.student.extension.lock]]
                            <div class="alert alert-danger">Student is locked from submission: [[detail.student.extension.lock_message]]</div>
                            [[else]]
                            <div class="alert alert-success">Student has extension.</div>
                            [[/detail.student.extension.lock]]
                            [[else]]
                            <div class="alert alert-warning">No extension granted.</div>
                            [[/detail.student.extension.updated_at]]
                            <label class="mt-2" for="extension_date">Expiration</label>
                            <input id="extension_date" class="flatpickr" data-id="extension_date" type="text" placeholder="Indefinite...">
                            <br/>
                            [[#detail.student.extension.updated_at]]
                            [[#!detail.student.extension.lock]]
                            <button class="btn btn-sm" on-click="['update_extension',true]">Update</button>
                            [[/detail.student.extension.lock]]
                            <button class="btn btn-sm" on-click="['update_extension',false]">Revoke</button>
                            [[else]]
                            <button class="btn btn-sm mt-2" on-click="['update_extension',true]">Grant Extension</button>
                            <hr>
                            <button class="btn btn-sm mt-2" on-click="['update_extension',true,{option: 'lock'}]">Lock Assignment</button>
                            <label class="sr-only" for="lockMsg">Lock Message</label>
                            <div class="input-group mb-2 mt-1 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Lock Message</div>
                                </div>
                                <input type="text" class="form-control" id="lockMsg" placeholder="Message explaining lockout." value="[[~/detail.student.extension.lock_message]]">
                            </div>
                            [[/detail.student.extension.updated_at]]
                            [[detail.extensionMsg !== null ? detail.extensionMsg : '']]
                        </div>
                    </div>
                </div>
                [[#detail.student.loadingMsg]]
                <div class="alert alert-danger">[[detail.student.loadingMsg]]</div>
                [[/detail.student.loadingMsg]]
                [[#detail.questions:q]]
                <div class="card mb-2">
                    <div class="card-header">[[name]]</div>
                    <div class="card-body">
                        First Submitted: [[~/detail.student.questions[q].result.created_at]]<br/>
                        Last Updated: [[~/detail.student.questions[q].result.updated_at]]<br/>
                        [[#type != 2]]
                            Attempts: [[~/detail.student.questions[q].result.attempts]]<br/>
                        [[else]]
                            [[#status == 1]]
                                Response Date: [[~/detail.student.questions[q].response.updated_at]]<br/>
                            [[/status]]
                        [[/type]]
                        [[~/detail.student.questions[q].result.earned]] / [[max_points]] points
                        <div style="width: 100%; overflow: auto;">
                            [[#type == 1]]
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    [[#variables:v]]
                                    <th>[[name]]</th>
                                    [[/variables]]
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    [[#variables:v]]
                                    <td>[[~/detail.student.questions[q].answers[id].submission]]</td>
                                    [[/variables]]
                                </tr>
                                </tbody>
                            </table>
                            [[elseif type == 2]]
                                <div class="card">
                                    <div class="card-body">
                                        [[&~/detail.student.questions[q].answers[0].submission]]
                                    </div>
                                </div>
                            [[else]]
                                Submission: [[~/detail.student.questions[q].answers[0].submission]]
                            [[/type]]
                        </div>
                        Feedback: [[~/detail.student.questions[q].result.feedback]]<br/>
                        [[#if type == 2 && ~/detail.student.questions[q].result && ~/detail.student.questions[q].result.status != 2]]
                        <button class="btn btn-sm btn-warning" on-click="['detailRetry',q]" id="Detail_Retry_[[q]]" >Retry</button>
                        [[/if]]
                        [[#if ~/detail.enableRefreshButtons && type == 1]]
                        <button class="btn btn-sm btn-outline-danger" on-click="['detailRefreshValues',q]" id="Detail_Retry_[[q]]" >Change Problem Values</button>
                        [[~/detail.student.refreshMsgs[q]]]
                        [[/if]]
                    </div>
                </div>
                [[/detail.questions]]
                <div class="card">
                    <div class="card-header">User Assignment Variable Cache</div>
                    <div class="card-body">
                        <div style="width: 100%; overflow: auto;">
                            <table class="table table-bordered">
                                [[#detail.student.cached_answer.values:v]]
                                <tr>
                                    <th>[[v]]</th> <td>[[this]]</td>
                                </tr>
                                [[/detail.student.cached_answer.values]]
                            </table>
                        </div>
                    </div>
                </div>
                <button class="btn btn-sm btn-outline-danger" on-click="@.toggle('detail.enableRefreshButtons')">[[detail.enableRefreshButtons ? 'Disable' : 'Enable']] Variable Refresh Buttons</button>
                Only use this feature if you know how it works and the consequences of using it incorrectly.
            </div>

        </div>
    </div>
</div>

[[#partial classroom]]

<div class="card card-default">
    <div class="card-header">
        <ul class="nav nav-pills">
            <li class="nav-item">
                <a class="nav-link [[selected_q == 'assignment' ? 'active' : '']]" name="item" on-click="['select_q','assignment']" id="[[rows.length-1]]" style="cursor:pointer;">Assignment</a>
            </li>
            [[#assignment.questions:q]]
            <li class="nav-item">
                <a class="nav-link [[selected_q == q ? 'active' : '']]" name="item" id="[[q]]" on-click="['select_q',q]" style="cursor:pointer;">[[name]]</a>
            </li>
            [[/assignment.questions]]

        </ul>
    </div>
    <div class="card-body">
        <canvas id="classCanvas"></canvas>
    </div>
    [[#noLocation.length != 0]]
    <div class="card-footer">
        No seat location found for: [[#noLocation:nL]][[firstname]] [[lastname]][[nL<~/noLocation.length-1 ? ', ' : '']] [[/noLocation]]
    </div>
    [[/noLocation]]
</div>

[[/partial]]

[[#partial values]]
<button class="btn btn-info btn-sm mt-1 mb-1" on-click="@.toggle('show_simple')">[[show_simple ? 'Hide' : 'Show']] Simple Questions</button>
<button class="btn btn-info btn-sm mt-1 mb-1" on-click="@.toggle('show_incorrect')">[[show_incorrect ? 'Hide' : 'Show']] Incorrect</button>
<button type="button" class="btn btn-info btn-sm mt-1 mb-1" on-click="['csv','values']" data-toggle="modal" data-target="#csv_modal">
    Export Values
</button>
[[#!allLoaded]]<button class="btn btn-outline-dark btn-sm mt-1 mb-1" on-click="load_all_values">Load All Values</button>[[/allLoaded]]
[[#valuesLoadingMsg]]<div class="alert alert-danger">[[valuesLoadingMsg]]</div>[[/valuesLoadingMsg]]
<table class="table table-hover">
    <thead>
    <tr>
        <th></th><th></th><th></th>
        [[#assignment.questions:q]]
        [[#type == 1]]
        <th class="text-center border-right border-left" colspan="[[variables.length]]">[[name]]
            [[#~/lazy && !~/allLoaded &&!loaded]]<br/><button class="btn btn-sm" on-click="['load_values',q]">Load</button>[[/~/lazy]]
            [[#loadingMsg]]<div class="alert alert-danger">[[loadingMsg]]</div>[[/loadingMsg]]
        </th>
        [[elseif type == 3 || type == 5 || type == 7]]
        [[#~/show_simple == true]]
        <th class="text-center">[[name]]
            [[#~/lazy]]<br/><button class="btn btn-sm" on-click="['load_values',q]">Load</button>[[/~/lazy]]
        </th>
        [[/~/show_simple]]
        [[/type]]
        [[/assignment.questions]]
    </tr>
    <tr>
        <th on-click="['sort','values','first']">First Name</th>
        <th on-click="['sort','values','last']">Last Name</th>
        <th on-click="['sort','values','seat']">Seat</th>
        [[#assignment.questions:q]]
            [[#type == 1]]
                [[#variables:v]]
                    <th><span on-click="['sort','variables',q+'.'+v,q,id]">[[title]]<br/>([[name]])</span><br/>
                        <button class="btn btn-sm [[shared ? 'btn-success' : 'btn-outline-dark']]" on-click="share">[[shared ? 'Unshare' : 'Share']]</button>
                    </th>
                [[/variables]]
            [[elseif [3,5,7].includes(type)]]
            [[#~/show_simple == true]]
                <th class="text-center" on-click="['sort','simple','simple_'+q,q]">Sort</th>
            [[/~/show_simple]]
            [[/type]]
        [[/assignment.questions]]
    </tr>
    </thead>
    <tbody>
    [[#each students:s]]
    <tr id="row_[[s]]">
        <td class="align-middle">
            [[firstname]]
        </td>
        <td class="align-middle">
            [[lastname]]
        </td>
        <td class="align-middle">
            [[pivot.seat]]
        </td>
        [[#questions:q]]
        [[#~/assignment.questions[@index].type == 1]]
        [[#each ~/assignment.questions[@index].variables:v]]
        <td class="align-middle text-center [[#if ~/students[s].questions[q].answers != undefined && ~/students[s].questions[q].result != undefined]] [[~/show_incorrect && ~/students[s].questions[q].result != null && ~/students[s].questions[q].answers[id] != undefined && ~/students[s].questions[q].result.earned == ~/assignment.questions[q].max_points ? '' : ~/students[s].questions[q].result.earned > 0 ? 'table-warning' : 'table-danger']] [[/if]]">
            [[#if ~/show_incorrect || ~/students[s].questions[q].answers[id] != undefined && ~/students[s].questions[q].result.earned == ~/assignment.questions[q].max_points]]
            [[~/students[s].questions[q].answers[id].submission]]
            [[/if]]
        </td>
        [[/each]]
        [[elseif [3,5,7].includes(assignment.questions[@index].type)]]
        [[#~/show_simple == true]]
        <td class="align-middle text-center [[#if ~/students[s].questions[q].answers != undefined && ~/students[s].questions[q].result != undefined]] [[~/show_incorrect && ~/students[s].questions[q].result != null && ~/students[s].questions[q].answers[0].earned != ~/assignment.questions[q].max_points ? 'table-danger' : '']] [[/if]]">
            [[#if ~/show_incorrect || ~/students[s].questions[q].result && ~/students[s].questions[q].result.earned == ~/assignment.questions[q].max_points || show_incorrect]]
            [[~/students[s].questions[q].answers[0].submission]]
            [[/if]]
        </td>
        [[/~/show_simple]]
        [[/assignment.questions[@index].type]]
        [[/questions]]

    </tr>
    [[/each]]
    </tbody>
</table>
[[/partial]]

[[#partial written]]

<button type="button" class="btn btn-info btn-sm mt-1 mb-1" on-click="['csv','written']" data-toggle="modal" data-target="#csv_modal">
    Export Submissions
</button>

<div class="row">
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header">
                <ul class="nav nav-pills">
                    [[#written:q]]
                    <li class="nav-item">
                        <a class="nav-link [[~/wq == q ? 'active' : '']]" name="item" id="W_[[q]]" on-click="['select_wq',q]" style="cursor:pointer;"><span class="align-middle">[[question.name]]</span> <span class="badge badge-pill align-middle [[unGraded[@index] > 0 ? 'badge-dark' : 'badge-success']]">[[unGraded[@index]]]</span></a>
                    </li>
                    [[/written]]

                </ul>
            </div>
            <div class="card-body">
                [[#wq == -1]]<div class="alert alert-danger">Click on a question name above to load answers.</div>[[/wq]]
                [[& written[wq].question.description]]
            </div>
            <div class="card-footer">[[written[wq].question.max_points]] points</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header">Options</div>
            <div class="card-body">
                <button class="btn btn-sm btn-info mb-1" on-click="@.toggle('showNames')">[[showNames ? 'Hide' : 'Show']] Names</button>
                <button class="btn btn-sm btn-info mb-1" on-click="@.toggle('showDates')">[[showDates ? 'Hide' : 'Show']] Dates</button>
                <button type="button" class="btn btn-info btn-sm mb-1" data-toggle="modal" data-target="#display_modal_[[wq]]">Display Options</button>
                <button type="button" class="btn btn-info btn-sm mb-1" data-toggle="modal" data-target="#response_modal_[[wq]]" on-click="init_response_modal">Edit Responses</button>
                <button type="button" class="btn btn-info btn-sm mb-1" data-toggle="modal" data-target="#values_modal_[[wq]]">View Values</button>
                <button class="btn btn-sm btn-info mb-1" on-click="save_options">[[saveOptionsMsg]]</button>
                <button class="btn btn-sm btn-outline-dark mb-1" on-click="compute_similarities">Compute Similarities</button>
                <br/>
                Feedback Release:
                <div class="dropdown mb-2 d-inline-block">
                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        [[written[wq].question.options == null || written[wq].question.options.releaseMode == undefined ||written[wq].question.options.releaseMode == 'immediate' ? 'Immediate' : 'Deferred']]
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item" href="#/" on-click="@.set('written.'+wq+'.question.options.releaseMode','immediate')">Immediate</a>
                        <a class="dropdown-item" href="#/" on-click="@.set('written.'+wq+'.question.options.releaseMode','deferred')">Deferred</a>
                    </div>
                </div>
                <button class="btn btn-sm btn-info mb-1" on-click="release_deferred">[[releaseMsg]]</button>
                <br/>
                Grading Mode:
                <div class="dropdown mb-2 d-inline-block">
                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        [[written[wq].question.options == null || written[wq].question.options.gradingMode == undefined || written[wq].question.options.gradingMode == 'fullHalf' ? 'Full/Half' : 'User Specified']]
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item" href="#/" on-click="@.set('written.'+wq+'.question.options.gradingMode','fullHalf')">Full/Half</a>
                        <a class="dropdown-item" href="#/" on-click="@.set('written.'+wq+'.question.options.gradingMode','specified')">User Specified</a>
                    </div>
                </div>
                [[#if written[wq].question.options != null && written[wq].question.options.gradingMode =='specified']]
                [[#each written[wq].question.options.gradeChoices]]
                <div class="input-group">
                    <input type="text" style='width: 120px' value="[[this]]">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" on-click="@.splice('written.'+wq+'.question.options.gradeChoices',@index,1)" type="button"><i class="far fa-trash-alt"></i></button>
                    </div>
                </div>
                [[/each]]
                <button class="btn btn-sm btn-success" on-click="@.push('written.'+wq+'.question.options.gradeChoices','')">Add Choice</button>
                [[/if]]
            </div>
        </div>
    </div>
</div>

[[#if written[wq].loading]]
<div class="alert alert-danger mt-2">
    [[written[wq].loadingMsg]]
</div>
[[/if]]

<a class="btn btn-sm mt-1" href="#graded_written">Jump to Graded</a>
<a class="btn btn-sm mt-1" href="#retry_written">Jump to Out for Retry</a>
<button class="btn btn-sm btn-info mt-1" on-click="@.toggle('batchGrade')">Batch Grade</button>
[[#batchGrade]]
<div class="card mt-1 mb-1">
    <div class="card-header">Batch Grade</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                Apply to selected: <div class="btn-group" role="group" aria-label="Application Group">
                    <button class="btn btn-outline-primary btn-sm mb-1" on-click="['applyBatch','score']">Score</button>
                    <button class="btn btn-outline-primary btn-sm mb-1" on-click="['applyBatch','feedback']">Feedback</button>
                    <button class="btn btn-outline-primary btn-sm mb-1" on-click="['applyBatch','all']">Score and Feedback</button>
                </div>
                <button class="btn btn-outline-primary mb-1" on-click="['submitBatch',1]">Submit Selected</button>
                <div></div>
                <button class="btn btn-sm btn-info mb-1" on-click="['selectAll',0,true]">Select All Ungraded</button>
                <button class="btn btn-sm btn-info mb-1" on-click="['selectAll',0,false]">Unselect All Ungraded</button>
                <button type="button" class="btn btn-outline-dark mb-1" data-toggle="modal" data-target="#batch_help">Help</button>
            </div>
            <div class="col-md-7">
                [[> response_list]]
            </div>
            <div class="col-md-1 pl-0 pr-2">
                [[> points_list]]
            </div>
        </div>
    </div>
</div>

[[/batchGrade]]

<h2 id="ungraded_written">Ungraded ([[unGradedwq[wq]]])</h2>
<div class="btn-group">
    <button class="btn btn-secondary btn-sm" on-click="['sort','written','created',wq]">Created</button>
    <button class="btn btn-secondary btn-sm" on-click="['sort','written','updated',wq]">Updated</button>
    <button class="btn btn-secondary btn-sm" on-click="['sort','written','first',wq]">First Name</button>
    <button class="btn btn-secondary btn-sm" on-click="['sort','written','last',wq]">Last Name</button>
    <button class="btn btn-secondary btn-sm" on-click="['sort','written','score',wq]">Score</button>
    [[#written[wq].similarity_computed]]
    <button class="btn btn-secondary btn-sm" on-click="['sort','written','similarity',wq]">Similarity</button>
    [[/written[wq].similarity_computed]]
</div>
[[#written[wq].entries:a]]
[[#result.status == 0]]
<div class="card mt-1 [[selected ? 'bg-primary' : '']]" >
    <div class="card-header" on-click="@context.toggle('selected')" >
        [[#selected]]<span class="badge badge-pill badge-secondary">Selected</span>[[/selected]][[#~/showNames]][[student.firstname]] [[student.lastname]][[/~/showNames]] [[#~/showDates]][[answer.updated_at]][[/~/showDates]]
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-7">
                [[& answer.submission]]
            </div>
            <div class="col-md-4">
                [[> response_list]]
            </div>
            <div class="col-md-1 pl-0 pr-2">
                [[> points_list]]
                [[#if ~/written[~/wq].question.options == null || ~/written[~/wq].question.options.gradingSubType == undefined || ~/written[~/wq].question.options.gradingSubType == 'individual']]
                <button type="button" class="btn btn-info btn-block" on-click="['submitSingle',1]">Submit</button>
                [[/if]]
                <button class="btn btn-sm btn-warning btn-block" on-click="['submitSingle',2]" id="Retry_[[a]]" >Retry</button>
            </div>
        </div>
    </div>
    [[#~/assignment.questions:q]]
    [[#if written_display[wq]]]
    <div class="card-footer">
        <b>[[~/assignment.questions[q].name]]</b><br/>
        [[&written[wq].entries[a].student.questions[q].answers[0].submission]]
    </div>
    [[/if]]
    [[#variables:v]]
    [[#if written_display[wq]]]
    <div class="card-footer">
    [[&title]] ([[name]])<br/> [[written[wq].entries[a].student.questions[q].answers[id].submission]]<br/>
    </div>
    [[/if]]
    [[/variables]]
    [[/~assignment.questions]]
    [[#~/written[wq].similarity_computed]]
    <div class="card-footer">
        Max similarity: [[similarity[0].similarity]]%
        <button class="btn btn-sm btn-secondary" on-click="@.set('simInd',a)" data-toggle="modal" data-target="#similarity_modal">Show all</button>
    </div>
    [[/~/written[wq].similarity_computed]]
</div>
[[/result.status]]
[[/written[wq].entries]]
<br/>
<a class="btn btn-sm mt-1" href="#ungraded_written">Jump to Ungraded</a>
<a class="btn btn-sm mt-1" href="#retry_written">Jump to Out for Retry</a>
<h2 id="graded_written">Graded</h2>
<div class="btn-group">
    <button class="btn btn-secondary btn-sm" on-click="['sort','written','created',wq]">Created</button>
    <button class="btn btn-secondary btn-sm" on-click="['sort','written','updated',wq]">Updated</button>
    <button class="btn btn-secondary btn-sm" on-click="['sort','written','first',wq]">First Name</button>
    <button class="btn btn-secondary btn-sm" on-click="['sort','written','last',wq]">Last Name</button>
    <button class="btn btn-secondary btn-sm" on-click="['sort','written','score',wq]">Score</button>
    [[#written[wq].similarity_computed]]
    <button class="btn btn-secondary btn-sm" on-click="['sort','written','similarity',wq]">Similarity</button>
    [[/written[wq].similarity_computed]]
</div>
<table class="table table-striped table-condensed">
    <thead>
    <th class="text-center">Student</th>
    <th class="text-center">Submission</th>
    [[#written[wq].similarity_computed]]
    <th class="">Max Similarity (%)</th>
    [[/written[wq].similarity_computed]]
    <th class="">Score</th>
    <th class="">Actions</th>
    </thead>
    <tbody>
    [[#written[wq].entries:a]]
    [[#result.status == 1 || result.status == 3]]
    <tr>
        <td>
            [[student.firstname]] [[student.lastname]]
        </td>
        <td>
            [[&answer.submission]]

            <br/>
            [[#result.status == 3]] <span class="badge badge-danger">Deferred</span>[[/result.status]] <strong>Feedback</strong>
            [[result.feedback]]

        </td>
        [[#~/written[wq].similarity_computed]]
        <td>
            [[similarity[0].similarity]]
            <button class="btn btn-sm btn-secondary" on-click="@.set('simInd',a)" data-toggle="modal" data-target="#similarity_modal">Show all</button>
        </td>
        [[/~/written[wq].similarity_computed]]
        <td>
            [[result.earned]]
        </td>
        <td>
            <button class="btn btn-warning" on-click="['submitSingle',2]" id="Retry_[[a]]" >Retry</button>
            <button class="btn btn-success" on-click="['submitSingle',0]" id="Regrade_[[a]]">Regrade</button>
        </td>
    </tr>
    [[/result.status]]
    [[/written[wq].entries]]
    </tbody>
</table>
<br/>
<a class="btn btn-sm mt-1" href="#ungraded_written">Jump to Unraded</a>
<a class="btn btn-sm mt-1" href="#graded_written">Jump to Graded</a>
<h2 id="retry_written">Out for Retry</h2>
<table class="table table-striped table-condensed">
    <thead>
    <th class="text-center">Student</th>
    <th class="text-center">Submission</th>
    <th class="">Score</th>
    <th class="">Actions</th>
    </thead>
    <tbody>
    [[#written[wq].entries:a]]
    [[#result.status == 2]]
    <tr>
        <td>
            [[student.firstname]] [[student.lastname]]
        </td>
        <td>
            [[&answer.submission]]
            <br/>
            <strong>Feedback</strong>
            [[result.feedback]]

        </td>
        <td>
            [[result.earned]]
        </td>
        <td>
            <button class="btn btn-warning" on-click="['submitSingle',0]" id="Recall_[[a]]" >Recall</button>
        </td>
    </tr>
    [[/result.status]]
    [[/written[wq].entries]]
    </tbody>
</table>

[[/partial]]

[[#partial response_list]]
<div class="response-box">
    <ul class="list-group">
        [[#each ~/written[~/wq].question.responses:r]]
        <li class="list-group-item py-1 [[~/showExpandedResponses ? '' : 'text-truncate']]" on-click="addResponse" id="[[q]]" name="[[a]]">
            [[response]]
        </li>
        [[/each]]
        [[#if ~/written[~/wq].question.responses.length == 0]]
        <p class="gray">No premade responses</p>
        [[/if]]
    </ul>
</div>
<div class="form-group mb-0">
                        <textarea value="[[result.feedback]]" style="width: 100%;" class="form-control">
                        </textarea>
</div>
<div class="small">
    <input type="checkbox" checked="[[result.save_response]]" name="save_response"  id="[[q]]"> Save Response
    <button class="btn btn-sm btn-info py-0 mb-1" on-click="@.toggle('~/showExpandedResponses')">[[~/showExpandedResponses ? 'Collapse' : 'Expand']] Responses</button>
</div>
[[/partial]]

[[#partial points_list]]
<div class="btn-group-vertical btn-block" role="group" aria-label="Points group">
    [[#if ~/written[~/wq].question.options == null || ~/written[~/wq].question.options.gradingMode == undefined || ~/written[~/wq].question.options.gradingMode == 'fullHalf']]
    <button type="button" class="btn [[result.earned == ~/written[~/wq].question.max_points ? 'btn-success' : 'btn-secondary']]" on-click="['addScore',~/written[~/wq].question.max_points]">Full</button>
    <button type="button" class="btn [[result.earned == ~/written[~/wq].question.max_points/2 ? 'btn-success' : 'btn-secondary']]" on-click="['addScore',~/written[~/wq].question.max_points/2]">Half</button>
    <button type="button" class="btn [[result.earned == 0 ? 'btn-success' : 'btn-secondary']]" on-click="['addScore',0]">Zero</button>
    [[elseif ~/written[~/wq].question.options != null && ~/written[~/wq].question.options.gradingMode == 'specified']]
    [[#each written[wq].question.options.gradeChoices]]
    <button type="button" class="btn [[result.earned == Number(this) ? 'btn-success' : 'btn-secondary']]" on-click="['addScore',Number(this),true]">[[this]]</button>
    [[/each]]
    [[/if]]
</div>
[[/partial]]

[[#partial quiz]]
<button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#quiz_settings_modal" on-click="@.set('quizSettingsMsg','')">
    Settings
</button>
<button type="button" class="btn btn-info btn-sm" on-click="@.set('allow_generate',false)" data-toggle="modal" data-target="#quiz_controls_modal">
    Controls
</button>
<button type="button" class="btn btn-info btn-sm mt-1 mb-1" on-click="['csv','quiz']" data-toggle="modal" data-target="#csv_modal">
    Export Timings
</button>
<button type="button" class="btn btn-outline-secondary btn-sm float-right mt-1" on-click="@.toggle('show_quiz_question_ids')">
    [[show_quiz_question_ids ? 'Hide' : 'Show']] Question IDs
</button>
<button type="button" class="btn btn-outline-secondary btn-sm float-right mt-1" on-click="@.toggle('quiz_batch')">
    Batch
</button>
[[#quiz_batch]]
<button type="button" class="btn btn-outline-secondary btn-sm float-right mt-1" on-click="show_quiz_batch_detail">
    Edit
</button>
<button type="button" class="btn btn-outline-secondary btn-sm float-right mt-1" on-click="quiz_select_none">
    Select None
</button>
<button type="button" class="btn btn-outline-secondary btn-sm float-right mt-1" on-click="quiz_select_all">
    Select All
</button>
[[/quiz_batch]]

<table class="table  table-hover">
    <thead>
    <tr>
        <th>&nbsp</th>
        <th on-click="['sort','quiz','first']">First Name</th>
        <th on-click="['sort','quiz','last']">Last Name</th>
        <th on-click="['sort','quiz','seat']">Seat</th>
        <th></th>
        <th on-click="['sort','quiz','actual_start']">Time Started</th>
        <th on-click="['sort','quiz','elapsed_time']">Elapsed Time (min)
            <i class="far fa-question-circle" aria-hidden data-toggle="tooltip" data-placement="right" title="Elapsed time will only appear if the student closes the quiz at the end rather than letting time expire."></i><span class="sr-only">Elapsed time will only appear if the student closes the quiz at the end rather than letting time expire.</span>
        </th>
        <th on-click="['sort','quiz','review_state']">Reviewable
            <i class="far fa-question-circle" aria-hidden data-toggle="tooltip" data-placement="right" title="When the quiz is reviewable, the student can see all questions assigned to them on a single page regardless of the quiz timing. They can only see feedback if the feedback has been released. They can only submit answers if their quiz is still open."></i><span class="sr-only">When the quiz is reviewable, the student can see all questions assigned to them on a single page regardless of the quiz timing. They can only see feedback if the feedback has been released. They can only submit answers if their quiz is still open.</span>
        </th>
        [[#show_quiz_question_ids]]
        [[#assignment.options.quiz.pages:p]]
        <th>Page [[p+1]]</th>
        [[/assignment.options.quiz.pages]]
        [[/show_quiz_question_ids]]

    </tr>
    </thead>
    <tbody>
    [[#each assignment.quiz_jobs:j]]
    <tr id="row_[[row_index]]" class="[[selected && ~/quiz_batch ? 'table-primary' : '']]" on-click="@.toggle('assignment.quiz_jobs.'+j+'.selected')">
        <td class="align-middle"><button class="btn btn-sm" on-click="show_quiz_detail" id="[[j]]"><i class="far fa-list-alt" aria-hidden  style="cursor:pointer;"></i><span class="sr-only">Customize Student Quiz</span></button></td>
        <td class="align-middle">
            [[user.firstname]]
        </td>
        <td class="align-middle">
            [[user.lastname]]
        </td>
        <td class="align-middle">
            [[getStudent(user.id).pivot.seat]]
        </td>
        <td class="align-middle">
            [[#allowed_minutes != ~/assignment.options.quiz.allowed_length]]<i class="far fa-clock" aria-hidden data-toggle="tooltip" title="Modified length: [[allowed_minutes]] minutes."></i><span class="sr-only">Modified length: [[allowed_minutes]] minutes.</span>[[/allowed_minutes]]
            [[#if allowed_start != ~/assignment.options.quiz.allowed_start || allowed_end != ~/assignment.options.quiz.allowed_end]]<i class="far fa-calendar-alt" aria-hidden data-toggle="tooltip" data-html="true" title="Modified dates.<br/>Start: [[allowed_start]]<br/>End: [[allowed_end]]"></i><span class="sr-only">Modified dates.  Start: [[allowed_start]], End: [[allowed_end]].</span>[[/if]]
        </td>
        <td class="align-middle">[[actual_start]]</td>
        <td class="align-middle">[[elapsed_time]]</td>
        <td class="align-middle">[[review_state == 1 ? 'Yes' : 'No']]</td>
        [[#~/show_quiz_question_ids]]
        [[#question_list:l]]
        <td>
            [[#this:p]]
                [[this]]
            [[/.]]
        </td>
        [[/question_list]]
        [[/~/show_quiz_question_ids]]
    </tr>
    [[/each]]
    </tbody>
</table>
[[#assignment.quiz_jobs.length==0]]
<p>
    No quiz jobs yet assigned. Use the settings button to establish quiz timing and layout, then use the controls button to generate quiz jobs for course users.
</p>
<p>
    After generating quiz jobs, you can customize timing for individual students.
</p>
[[/assignment.quiz_jobs.length]]
[[/partial]]

<!-- Modal for csv -->
<div id="csv_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Results Export</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>You can copy and paste this into a spreadsheet:</p>
                <textarea class ="form-control" id="modal_csv"
                          value="[[csv_text]]">
                        </textarea>
                <br><p>Or click save to download the file.</p>
            </div>
            <div class="modal-footer">
                <button type="button" on-click="csv_download" class="btn btn-primary" id="save_csv">Save</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for rescoring -->
<div id="rescore_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Question Rescoring</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                [[#assignment.linked_assignments_count>0]]
                <div class="alert alert-info text-center">
                    <p><i class="fas fa-link"></i> This is a linked assignment with [[assignment.linked_assignments_count]] children.</p>
                    [[#linked]]
                    <p>The controls below will apply to all linked assignments. To apply only to this assignment and not to children, visit the main parent results.</p><p><a class="btn btn-sm btn-outline-dark" href="main">View Parent Only</a></p>
                    [[else]]
                    <p>The controls below will apply only to this assignment. To affect all linked assignments, visit the linked results.</p><p><a class="btn btn-sm btn-outline-dark" href="linked">View All Linked</a></p>
                    [[/linked]]
                </div>
                [[/assignment.linked_assignments_count]]
                <p>This will rescore a question for all users using their current inputs. It is designed to allow you to update scores if you made an error in your conditions and you don't want every student to have to hit evaluate again. Not available for molecule questions.</p>
                <table class="table">
                    [[#assignment.questions:q]]
                    [[#if ![2,4,6].includes(type)]]  <!-- Don't include written, info blocks, or molecule questions-->
                    <tr><td>[[name]]</td><td><button class="btn btn-sm btn-info" on-click="['rescore_question',q]">Rescore</button></td></tr>
                    [[/if]]
                    [[/assignment.questions]]
                </table>
            </div>
            <div class="modal-footer">
                <div class="text-left">[[&rescoreMsg]]</div>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for deferred feedback -->
<div id="deferred_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Deferred Feedback Controls Controls</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                [[#assignment.linked_assignments_count>0]]
                <div class="alert alert-info text-center">
                    <p><i class="fas fa-link"></i> This is a linked assignment with [[assignment.linked_assignments_count]] children.</p>
                    [[#linked]]
                    <p>The controls below will apply to all linked assignments. To apply only to this assignment and not to children, visit the main parent results.</p><p><a class="btn btn-sm btn-outline-dark" href="main">View Parent Only</a></p>
                    [[else]]
                    <p>The controls below will apply only to this assignment. To affect all linked assignments, visit the linked results.</p><p><a class="btn btn-sm btn-outline-dark" href="linked">View All Linked</a></p>
                    [[/linked]]
                </div>
                [[/assignment.linked_assignments_count]]
                <a href="[[linked ? 'release_deferred_feedback_including_linked' : 'release_deferred_feedback']]" class="btn btn-sm btn-info mb-1">Release Deferred Feedback</a>
                <a href="[[linked ? 'redefer_feedback_including_linked' : 'redefer_feedback']]" class="btn btn-sm btn-info mb-1">Move Feedback to Deferred State</a>
                <br/>
                Note that moving feedback to the deferred state only applies to questions set to use deferred feedback.
            </div>

        </div>
    </div>
</div>

<!-- Modal for written response editing -->
[[#written:w]]
<div id="response_modal_[[w]]" class="modal fade" role="dialog" tabindex="-1">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"></h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <h4>Prewritten Responses : <button on-click="newResponse" class="btn btn-success btn-sm">Add response</button></h4>
                <ul class="list-group" id="response_list_[[w]]">
                    [[#each question.responses:r]]
                    [[#!to_delete]]
                    <li class="list-group-item py-0 p-0" data-id="[[r]]">
                        <span class="input-group-text response-handle"><i class="fas fa-arrows-alt"></i></span>
                        <textarea class="form-control row-fluid" id="[[html_id]]" style="min-width: 380px"  value="[[response]]"></textarea>
                        <button class="btn btn-warning btn-sm float-right " on-click="@context.toggle('to_delete')">Remove</button>
                    </li>
                    [[/!to_delete]]
                    [[/each]]
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" on-click="save_responses" class="btn btn-primary">[[saveResponseMsg]]</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
            [[#saveResponseError]]
            <div class="modal-footer">
                <div class="alert alert-danger d-block">[[saveResponseError]]</div>
            </div>
            [[/saveResponseError]]
        </div>
    </div>
</div>

<div id="display_modal_[[w]]" class="modal fade" role="dialog" tabindex="-1">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Display Options</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <label for="img_width_[[w]]">Image Width (px)</label>
                <input id="img_width_[[w]]" type="text" style='width: 100px' value="[[img_width]]">
                <button class="btn btn-sm btn-info" on-click='["apply_img_width",w]'>Apply</button>
                <br/>
                <label for="response_box_height_[[w]]">Response Box Height (px)</label>
                <input id="response_box_height_[[w]]" type="text" style='width: 100px' value="[[response_box_height]]">
                <button class="btn btn-sm btn-info" on-click='["apply_response_box_height",w]'>Apply</button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div id="values_modal_[[w]]" class="modal fade" role="dialog" tabindex="-1">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Values to View</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Written questions below will be displayed below each response when grading. Note that you must click on each written question to load the responses.</p>
                [[#assignment.questions:q]]
                [[#type == 2]]
                <button class="btn btn-sm [[written_display[w] ? 'btn-success' : '']]" on-click="['written_other_display',w,q]" id="w_[[w]]values__q_[[q]]">[[written_display === undefined ? 'Off' : written_display[w] === true ? 'On' : 'Off']]</button>
                <label for="w_[[w]]values__q_[[q]]">
                    [[name]]
                </label><br/>
                [[/type]]
                [[/assignment.questions]]
                <hr>
                <p>Values you turn on here will be displayed below each response when grading. Note that you must got to the values tab and load the values for them to appear.</p>
                [[#assignment.questions:q]]
                [[#type == 1]]
                <div class="block"><b>[[name]]</b></div>
                [[#variables:v]]
                <button class="btn btn-sm [[written_display[w] ? 'btn-success' : '']]" on-click="['written_value',w,q,v]" id="w_[[w]]values__q_[[q]]_v_[[v]]">[[written_display === undefined ? 'Off' : written_display[w] === true ? 'On' : 'Off']]</button>
                <label for="w_[[w]]values__q_[[q]]_v_[[v]]">
                    [[title]] ([[name]])
                </label><br/>

                [[/variables]]
                [[/type]]
                [[/assignment.questions]]
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
[[/written]]

<!-- Modal for written similarity ananlysis -->
<div class="modal fade" id="similarity_modal" tabindex="-1">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeModalLabel">Similarity Analysis</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>This uses a simple string cosine similarity algorithm. HTML tags are not currently removed in the analysis.</p>
                <p>Student: [[written[wq].entries[simInd].student.firstname]] [[written[wq].entries[simInd].student.lastname]] </p>
                <p>Submission: [[written[wq].entries[simInd].stripped_submission]]</p>
                <table class="table table-striped table-condensed">
                    <tr>
                        <th>Similarity (%)</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Submission</th>
                    </tr>
                    [[#written[wq].entries[simInd].similarity:s]]
                    <tr>
                        <td>[[similarity]]</td>
                        <td>[[firstname]]</td>
                        <td>[[lastname]]</td>
                        <td>[[submission]]</td>
                    </tr>
                    [[/written[wq].entries[simInd].similarity]]
                </table>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Modal for batch grading help -->
<div class="modal fade" id="batch_help">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeModalLabel">Batch Grading</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Process for batch grading:</p>
                <ul>
                    <li>Select the items you want to grade.  Click the gray heading for each answer that you want to receive the same feedback.</li>
                    <li>In the batch grading area, select the feedback and the score that you want.</li>
                    <li>Click the "Apply to Selected" button to set the feedback and score on each of the selected items.</li>
                    <li>Click the "Submit Selected" button to submit all of the selected items.  Note that if you click submit without first applying the feedback and score, the items will submit with whatever was already applied to them (if anything).</li>
                </ul>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Modal for quiz settings -->
<div id="quiz_settings_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Quiz Settings</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                [[#assignment.linked_assignments_count>0]]
                <div class="alert alert-info text-center">
                    <p><i class="fas fa-link"></i> This is a linked assignment with [[assignment.linked_assignments_count]] children. Changes to these settings will be applied to all child assignments.</p>
                    <p>Child assignments can modify these settings, but they will be overridden anytime this assignment is saved (from here or the editor). Settings are only applied to the quiz jobs at the time of quiz job generation.</p>
                </div>
                [[/assignment.linked_assignments_count]]
                [[#assignment.parent_assignment_id != null]]
                <div class="alert alert-warning text-center">
                    <p><i class="fas fa-link"></i> This is a linked assignment that belongs to a parent assignment.</p>
                    <p>You can modify these settings for the purposes of generating or updating quiz jobs. These settings will be overridden anytime the parent assignment is updated, but quiz jobs that have already been generated will not be affected.</p>
                </div>
                [[/assignment.parent_assignment_id]]
                <label> Start:</label>
                <input id="quiz_start_all" class="flatpickr" data-id="quiz_start_all" type="text" placeholder="Select Date..">
                <label> End:</label>
                <input id="quiz_end_all" class="flatpickr" data-id="quiz_end_all" type="text" placeholder="Select Date..">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">Allowed minutes</span>
                    </div>
                    <input type="number" class="form-control" value="[[assignment.options.quiz.allowed_length]]">
                </div>
                <br/>
                <label for="quiz_instructions">Instructions:</label>
                <div class='row'>
                    <div class='col-md-12 tex2jax_ignore'>
                        <div id="quiz_instructions">[[[assignment.options.quiz.instructions]]]</div>
                    </div>
                </div>
                <button class="btn btn-sm btn-info" on-click="update_quiz_settings">Update</button>
                [[quizSettingsMsg]]
            </div>

            <div class="card">
                <div class="card-header">Quiz Layout</div>
                <div class="card-body">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" [[assignment.options.quiz.shuffle_pages ? 'checked' : '']] id="quiz_shuffle_pages_toggle" on-click="@.toggle('assignment.options.quiz.shuffle_pages')">
                        <label class="form-check-label" for="quiz_shuffle_pages_toggle">
                            Shuffle pages
                            <i class="far fa-question-circle" aria-hidden data-toggle="tooltip" data-placement="right"
                               title="If selected, pages will be assigned a random order for each student. If unselected, pages will appear in the order listed below."
                            ></i>
                            <span class="sr-only">
                                If selected, pages will be assigned a random order for each student. If unselected, pages will appear in the order listed below.
                            </span>
                        </label>
                    </div>
                    [[#assignment.options.quiz.pages:p]]
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-1">
                                Page [[p+1]] <button class="btn btn-sm btn-warning" on-click="['dropQuizPage',p]">Drop page</button>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" [[shuffle_groups ? 'checked' : '']] id="quiz_shuffle_groups_toggle_[[p]]" on-click="@.toggle('assignment.options.quiz.pages.'+p+'.shuffle_groups')">
                                    <label class="form-check-label" for="quiz_shuffle_groups_toggle_[[p]]">
                                        Shuffle groups
                                        <i class="far fa-question-circle" aria-hidden data-toggle="tooltip" data-placement="right"
                                           title="If selected, groups on the page will be assigned a random order for each student, though questions within groups will remain together. If unselected, groups will appear in the order listed below."
                                        ></i>
                                        <span class="sr-only">
                                            If selected, groups on the page will be assigned a random order for each student, though questions within groups will remain together. If unselected, groups will appear in the order listed below.
                                        </span>
                                    </label>
                                </div>
                            </div>
                            [[#groups:g]]
                            <div class="card">
                                <div class="card-body">
                                    <div class="mb-1">
                                        Group [[g+1]] <button class="btn btn-sm btn-warning" on-click="['dropQuizGroup',p,g]">Drop group</button>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="" [[shuffle_within_group ? 'checked' : '']] id="quiz_shuffle_within_group_toggle_[[p]]_[[g]]" on-click="@.toggle('assignment.options.quiz.pages.'+p+'.groups.'+g+'.shuffle_within_group')">
                                            <label class="form-check-label" for="quiz_shuffle_within_group_toggle_[[p]]_[[g]]">
                                                Shuffle questions within group
                                                <i class="far fa-question-circle" aria-hidden data-toggle="tooltip" data-placement="right"
                                                   title="If selected, chosen questions will be assigned a random order for each student. If unselected, questions that are chosen will appear in the order listed below."
                                                ></i>
                                                <span class="sr-only">
                                                    If selected, chosen questions will be assigned a random order for each student. If unselected, questions that are chosen will appear in the order listed below.
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="input-group mb-1">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Number to select</span>
                                        </div>
                                        <input type="number" class="form-control" value="[[selection_number]]">
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <i class="far fa-question-circle" aria-hidden data-toggle="tooltip" data-placement="right"
                                                   title="Number of questions to be included in the group. The questions are randomly selected from those that are highlighted in the list below. Questions will only be selected once per quiz, so it is safe to highlight the same question in multiple groups or pages. Use the shift/control/command keys to select multiple questions."
                                                ></i>
                                                <span class="sr-only">
                                                    Number of questions to be included in the group. The questions are randomly selected from those that are highlighted in the list below. Questions will only be selected once per quiz, so it is safe to highlight the same question in multiple groups or pages. Use the shift/control/command keys to select multiple questions.
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="page_[[p]]_group_[[g]]_select">Select possible questions</label>
                                        <select multiple class="form-control" id="page_[[p]]_group_[[g]]_select" value="[[question_ids]]">
                                            [[#~/assignment.questions:q]]
                                            <option value="[[id]]">[[name]]</option>
                                            [[/~/assignment.questions]]
                                        </select>
                                    </div>
                                </div>
                            </div>
                            [[/groups]]
                            <button class="btn btn-sm btn-info" on-click="['addQuizGroup',p]">Add group</button>
                            <i class="far fa-question-circle" aria-hidden data-toggle="tooltip" data-placement="right"
                               title="Groups can be used to keep questions grouped together when shuffling items on the page. For the simplest layout, use a single group per page."
                            ></i>
                            <span class="sr-only">
                                Groups can be used to keep questions grouped together when shuffling items on the page. For the simplest layout, use a single group per page.
                            </span>
                        </div>
                    </div>
                    [[/assignment.options.quiz.pages]]
                    <button class="btn btn-sm btn-info" on-click="addQuizPage">Add Page</button>
                    <i class="far fa-question-circle" aria-hidden data-toggle="tooltip" data-placement="right"
                       title="Currently, once students press the button to complete a page, they are unable to return to the page. You can put as many questions as you want on a page."
                    ></i>
                    <span class="sr-only">
                        Currently, once students press the button to complete a page, they are unable to return to the page. You can put as many questions as you want on a page.
                    </span>
                </div>
            </div>
            <div class="modal-body">
                <button class="btn btn-sm btn-info" on-click="update_quiz_settings">Update</button>
                [[quizSettingsMsg]]
            </div>
        </div>
    </div>
</div>

<!-- Modal for quiz controls -->
<div id="quiz_controls_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Quiz Controls</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                [[#assignment.linked_assignments_count>0]]
                <div class="alert alert-info text-center">
                    <p><i class="fas fa-link"></i> This is a linked assignment with [[assignment.linked_assignments_count]] children.</p>
                    [[#linked]]
                    <p>The controls below will apply to all linked assignments. To apply only to this assignment and not to children, visit the main parent results.</p><p><a class="btn btn-sm btn-outline-dark" href="main">View Parent Only</a></p>
                    [[else]]
                    <p>The controls below will apply only to this assignment. To affect all linked assignments, visit the linked results.</p><p><a class="btn btn-sm btn-outline-dark" href="linked">View All Linked</a></p>
                    [[/linked]]
                </div>
                [[/assignment.linked_assignments_count]]
                [[#assignment.quiz_jobs.length==0]]
                <a href="[[linked ? 'generate_quizzes_including_linked' : 'generate_quizzes']]" class="btn btn-sm btn-info mb-2 [[quizSettingsComplete ? '' : 'disabled']]" >Generate Quiz Jobs</a>
                [[#!quizSettingsComplete]]Required quiz settings have not been set. You must set and update the start and end times, the length, and the instructions.[[/quizSettingsComplete]]
                [[else]]
                <a href="[[linked ? 'generate_missing_quizzes_including_linked' : 'generate_missing_quizzes']]" class="btn btn-sm btn-info mb-2 [[quizSettingsComplete ? '' : 'disabled']]" >Generate Missing Quiz Jobs</a>
                <i class="far fa-question-circle" aria-hidden data-toggle="tooltip" data-placement="right" title="Generate missing quiz jobs will generate jobs for users who do not yet have a job. Existing quiz jobs will be unaffected."></i>
                <span class="sr-only">Generate missing quiz jobs will generate jobs for users who do not yet have a job. Existing quiz jobs will be unaffected.</span>
                <br/>
                <button class="btn btn-sm btn-outline-info mb-2" on-click="@.set('allow_generate',true)">Enable regeneration button</button>
                <a href="[[linked ? 'generate_quizzes_including_linked' : 'generate_quizzes']]" class="btn btn-sm btn-info mb-2 [[allow_generate ? '' : 'disabled']]" >Regenerate Quiz Jobs</a>
                <i class="far fa-question-circle" aria-hidden data-toggle="tooltip" data-placement="right" title="Pressing regenerate will create quizzes for all course users. Existing quiz jobs will be deleted (though existing answers to questions will not)."></i>
                <span class="sr-only">Pressing regenerate will create quizzes for all course users. Existing quiz jobs will be deleted (though existing answers to questions will not).</span>
                <br/>
                <a href="[[linked ? 'update_quiz_timings_including_linked' : 'update_quiz_timings']]" class="btn btn-sm btn-info">Update Timings</a>
                <i class="far fa-question-circle" aria-hidden data-toggle="tooltip" data-placement="right" title="Will update the timings only (allowed start, end, length) for all quiz jobs based on the values in the settings. Overwrites any individual settings, but does not affect existing question layouts for jobs."></i>
                <span class="sr-only">Will update the timings only (allowed start, end, length) for all quiz jobs based on the values in the settings. Overwrites any individual settings, but does not affect existing question layouts for jobs.</span>
                [[/assignment.quiz_jobs.length]]
                <p></p>
                <a href="[[linked ? 'allow_quiz_review_including_linked' : 'allow_quiz_review']]" class="btn btn-sm btn-info mb-1">Allow Quiz Review</a>
                <a href="[[linked ? 'disallow_quiz_review_including_linked' : 'disallow_quiz_review']]" class="btn btn-sm btn-info mb-1">Disallow Quiz Review</a>
                <br/>
                <a href="[[linked ? 'release_deferred_feedback_including_linked' : 'release_deferred_feedback']]" class="btn btn-sm btn-info mb-1">Release Deferred Feedback</a>
                <a href="[[linked ? 'redefer_feedback_including_linked' : 'redefer_feedback']]" class="btn btn-sm btn-info mb-1">Move Feedback to Deferred State</a>
            </div>

        </div>
    </div>
</div>

<!-- Modal for quiz detail -->
<div id="quiz_detail_modal" class="modal fade" role="dialog"  tabindex="-1">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Student Details</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                [[#linked]]
                <div class="alert alert-danger">
                    <strong>Caution!</strong> The buttons on this dialog may not work correctly for linked assignments. It is advised that you make any changes for individual students from the child course rather than from here.
                </div>
                [[/linked]]
                <div class="card mb-2">
                    <div class="card-header">Student</div>
                    <div class="card-body">
                        [[quiz_detail.user.firstname]] [[quiz_detail.user.lastname]]<br/>
                        [[quiz_detail.user.email]]<br/>
                        Seat: [[quiz_detail.user.pivot.seat]]<br/>
                    </div>
                </div>
                <label> Start:</label>
                <input id="quiz_start_ind" class="flatpickr" data-id="quiz_start_ind" type="text" placeholder="Select Date..">
                <label> End:</label>
                <input id="quiz_end_ind" class="flatpickr" data-id="quiz_end_ind" type="text" placeholder="Select Date..">
                <div class="input-group mb-1">
                    <div class="input-group-prepend">
                        <span class="input-group-text">Allowed minutes</span>
                    </div>
                    <input type="number" class="form-control" value="[[quiz_detail.allowed_minutes]]">
                </div>
                <button class="btn btn-sm btn-info" on-click="update_student_quiz_detail">Update</button>
                [[quizDetailsMsg]]
                <hr>
                <span class="mb-1">
                    Quiz Status: [[quiz_detail.status === 0 ? 'Not Started' : quiz_detail.status === 1 ? 'Started' : 'Complete']]
                    [[#quiz_detail.status === 2]]<button class="btn btn-sm btn-outline-primary" on-click="['update_student_quiz_status',1]">Re-open</button>[[/quiz_detail.status]]
                </span>
                [[#quiz_detail.status > 0]]
                <div>
                    Actual Start: [[quiz_detail.actual_start]]
                </div>
                <div class="mb-1">
                    Elapsed Time (min): [[quiz_detail.elapsed_time]]
                </div>
                [[/quiz_detail.status]]
                <div class="mb-1">
                    Reviewable: [[quiz_detail.review_state === 1 ? 'Yes' : 'No']] <button class="btn btn-sm" on-click="toggle_student_review_state">Toggle</button>
                </div>
                <div class="mb-1">
                    Feedback:
                    <button class="btn btn-sm" on-click="['update_student_deferred_results_state',1,3]">Defer</button>
                    <button class="btn btn-sm" on-click="['update_student_deferred_results_state',3,1]">Release</button>
                </div>

                [[#quiz_detail.question_list:p]]
                <div class="card">
                    <div class="card-header">Page: [[p+1]]</div>
                    <div class="card-body">
                        Status: [[status === 0 ? 'Not Started' : status === 1 ? 'Started' : 'Complete']]
                        [[#status === 2]]<button class="btn btn-sm btn-outline-primary" on-click="['update_student_quiz_page_status',p,1]">Re-open</button>[[/quiz_detail.status]]
                        <div class="list-group">
                            [[#ids:i]]
                            <div class="list-group-item">[[getQuestionName(this)]]</div>
                            [[/ids]]
                        </div>
                        <div>
                            Start Time: [[start_time]]
                        </div>
                        <div>
                            Finish Time: [[finish_time]]
                        </div>
                    </div>
                </div>
                [[/quiz_detail.question_list]]
                <hr>
                Pressing regenerate will create a new quiz job for this user. The existing quiz job will be deleted (though existing answers to questions will not).
                <button class="btn btn-sm btn-outline-info mb-2" on-click="@.set('quiz_detail.allow_regenerate',true)">Enable regeneration button</button>
                <a href="generate_quiz_for_student/[[quiz_detail.user.id]]" class="btn btn-sm btn-info mb-2 [[quiz_detail.allow_regenerate ? '' : 'disabled']]" >Regenerate Quiz Job</a>
            </div>

        </div>
    </div>
</div>

<!-- Modal for quiz batch edit -->
<div id="quiz_batch_detail_modal" class="modal fade" role="dialog"  tabindex="-1">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Quiz Job Batch Edit</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                [[#linked]]
                <div class="alert alert-danger">
                    <strong>Caution!</strong> The buttons on this dialog may not work correctly for linked assignments. It is advised that you make any changes for individual students from the child course rather than from here.
                </div>
                [[/linked]]
                <label> Start:</label>
                <input id="quiz_start_batch" class="flatpickr" data-id="quiz_start_batch" type="text" placeholder="Select Date..">
                <label> End:</label>
                <input id="quiz_end_batch" class="flatpickr" data-id="quiz_end_batch" type="text" placeholder="Select Date..">
                <!--<div class="input-group mb-1">
                    <div class="input-group-prepend">
                        <span class="input-group-text">Allowed minutes</span>
                    </div>
                    <input type="number" class="form-control" value="[[quiz_detail.allowed_minutes]]">
                </div> -->
                <button class="btn btn-sm btn-info" on-click="update_batch_quiz_detail">Update</button>
                [[quizDetailsMsg]]
            </div>

        </div>
    </div>
</div>

<!-- Modal for quiz batch edit -->
<div id="stats_modal" class="modal fade" role="dialog"  tabindex="-1">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Statistics</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <ul>
                    <li>N: [[stats.basics.num]]</li>
                    <li>Mean: [[stats.basics.mean]]</li>
                    <li>Std. Dev.: [[stats.basics.stdev]]</li>
                    <li>Median: [[stats.basics.median]]</li>
                    <li>[[stats.basics.excluded]] zeroes excluded from stats.</li>
                    Instructors are excluded from stats.
                </ul>
            </div>
            <div id="stats_chart"  style="width:95%; height:200px;"></div>
        </div>
    </div>
</div>
