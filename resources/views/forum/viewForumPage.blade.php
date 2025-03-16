<div class="container">
    <div class="row">
        <div class="col-md-12">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif
            <!-- Show topic -->
            <div class="card card-info mb-2">
                <div class="card-header text-white bg-info">
                    [[[forum.title]]]
                </div>
                <div class="card-body">
                    [[[ forum.question ]]]
                </div>
                <div class="card-footer">
                    [[#if owner==1 || instructor==1]]
                    <a href="../edit/[[forum.id]]" class="btn btn-outline-dark btn-sm">Edit</a>

                    <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#modal_delete_forum_[[forum.id]]">Delete</button>

                    <!-- Modal to confirm the removal of a forum-->
                    <div class="modal fade" id="modal_delete_forum_[[forum.id]]">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Warning</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                </div>
                                <div class="modal-body">
                                    <p>Deleting this discussion topic will remove the entire conversation, including all responses.  Consider leaving it for the benefit of others.  This action cannot be undone.</p>
                                    <p>Are you sure you want to delete this topic?</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-dark" data-dismiss="modal">Cancel</button>
                                    <a href="../delete/[[forum.id]]" class="btn btn-danger">Delete</a>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                    [[/if]]
                    <span  [[#if instructor==1]]data-toggle='tooltip' data-placement='top' title='[[forum_identity]]'[[/if]]>Posted [[forum.time]] by [[author]]</span>
                </div>
            </div>

            <!-- Answer controls -->
            <div class="card mb-2">
                <div class="card-body">
                    Sort responses by:
                    [[#if sortBy=="oldest"]]
                    <button class="btn btn-primary" id="oldest" on-click="sort">Oldest First</button>
                    [[else]]
                    <button class="btn btn-outline-dark" id="oldest" on-click="sort">Oldest First</button>
                    [[/if]]
                    [[#if sortBy=="created_at"]]
                    <button class="btn btn-primary" id="created_at" on-click="sort">Newest First</button>
                    [[else]]
                    <button class="btn btn-outline-dark" id="created_at" on-click="sort">Newest First</button>
                    [[/if]]
                    [[#if sortBy=="votes"]]
                    <button class="btn btn-primary" id="votes" on-click="sort">Most Helpful</button>
                    [[else]]
                    <button class="btn btn-outline-dark" id="votes" on-click="sort">Most Helpful</button>
                    [[/if]]
                    <!--<div>
                        You are [[subscribed ? '' : 'not']] subscribed to email updates for this topic.
                        <button class="btn btn-info btn" id="subscribe" on-click="subscribe">[[subscribed ? 'Unsubscribe' : 'Subscribe']]</div>
                    </div>-->
                </div>
            </div>

            <!-- Add an answer -->
            [[#if answering]]
            <div class="card mb-2" id="responsePanel" name="responsePanel">
                <div class="card-header">
                    Add a response
                </div>
                <div class="card-body tex2jax_ignore">
                    <div id="editor">
                        [[[forum_question]]]
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary" id="save" on-click="save" on-keypress="save">[[saving]]</button>
                    <button class="btn btn-info" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                        Help with equations
                    </button>
                    <div class="form-check-inline ml-2">
                        <input class="form-check-input" id="anonymousCheck" type="checkbox" value=[[anonymous]] on-click="check" [[#if anonymous]]checked[[/if]]>
                        <label for="anonymousCheck" class="form-check-label">
                            Post anonymously. <a href="#/" class="text-decoration-none" data-toggle='tooltip' data-placement='right' title='Your instructor will know your identity, but you will be anonymous to your classmates.  Uncheck to show your name to your classmates.'><i class="fas fa-question-circle" aria-hidden ></i></a><span class="sr-only">Your instructor will know your identity, but you will be anonymous to your classmates.  Uncheck to show your name to your classmates.</span>
                        </label>
                    </div>
                    @include('forum.MathHelp')
                </div>
            </div>
            [[else]]
            <div class="card mb-2">
                <div class="card-body">
                    [[#if !answering]]<button class="btn btn-outline-dark" id="additional" on-click="additional">Add a response</button>[[/if]]
                </div>
            </div>
            [[/if]]

            <!-- Show responses -->
            [[#responses:r]]
            <div class="card card-default mb-2">
                <div class="card-header">
                    [[#if owner==1]]
                    Your Answer
                    [[else]]
                    Student Answer
                    [[/if]]
                </div>
                [[#if editing]]
                <div class="card-body tex2jax_ignore">
                    <div id="editor2">
                        [[[answer]]]
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary" id="[[r]]" on-click="updateResponse" on-keypress="updateResponse">[[responseUpdate]]</button>
                    <button class="btn btn-outline-dark" id="[[r]]" on-click="cancelEdit" on-keypress="cancelEdit">Cancel</button>
                </div>
                [[else]]
                <div class="card-body">
                    <div id="answer_[[r]]">
                    [[[answer]]]
                    </div>
                </div>
                <div class="card-footer">
                    [[#if owner==1 || instructor==1]]
                    [[#if votes > 0]]
                    <button class="btn btn-success disabled">Helpful <span class="badge">[[votes]]</span></button>
                    [[else]]
                    <button class="btn btn-outline-dark disabled">Helpful <span class="badge">0</span></button>
                    [[/if]]
                    [[#if endorsed==1 && instructor !=1]]
                    <button class="btn btn-warning disabled">Endorsed by Instructor</button>
                    [[/if]]
                    [[#if instructor==1]]
                    [[#if endorsed==1]]
                    <button class="btn btn-warning" on-click="endorse" id="[[r]]">Endorsed</button>
                    [[else]]
                    <button class="btn btn-outline-dark"  on-click="endorse" id="[[r]]">Endorse</button>
                    [[/if]]
                    [[/if]]
                    <button class="btn btn-sm btn-outline-dark" id="[[r]]" on-click="editResponse" on-keypress="editResponse">Edit</button>
                    <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#modal_delete_forum_answer_[[id]]">Delete</button>
                    [[else]]
                    [[#if votes > 0]]
                    <button class="btn btn-success" id="vote[[r]]" on-click="responseVote">[[#if voted]]Marked Helpful[[else]]Mark Helpful[[/if]] <span class="badge">[[votes]]</span></button>
                    [[else]]
                    <button class="btn btn-outline-dark" id="vote[[r]]" on-click="responseVote">[[#if voted]]Marked Helpful[[else]]Mark Helpful[[/if]] <span class="badge">0</span></button>
                    [[/if]]
                    [[#if endorsed==1 && instructor!=1]]
                    <button class="btn btn-warning disabled">Endorsed by Instructor</button>
                    [[/if]]
                    [[/if]]
                    <span  [[#if instructor==1]]data-toggle='tooltip' data-placement='top' title='[[identity]]'[[/if]]>Posted [[time]] by [[respondent]]</span>
                </div>
                <!-- Modal to confirm the removal of an answer-->
                <div class="modal fade" id="modal_delete_forum_answer_[[id]]">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Warning</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            </div>
                            <div class="modal-body">
                                <p>This action cannot be undone.  Consider leaving it for the benefit of others.</p>
                                <p>Are you sure you want to delete this response?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-dark" data-dismiss="modal">Cancel</button>
                                <a href="../deleteResponse/[[id]]" class="btn btn-danger">Delete</a>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->
                [[/if]]
            </div>
            [[/responses]]

            [[#if !answering && responses.length > 0]]
            <div class="card card-primary">
                <div class="card-body">
                    [[#if !answering]]<button class="btn btn-outline-dark" id="additional" on-click="additional">Add a response</button>[[/if]]
                </div>
            </div>
            [[/if]]
        </div>
    </div>
</div>
