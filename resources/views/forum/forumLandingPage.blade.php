<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h3>Forum Page for [[course.name]]</h3>
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif
            <!--div class="alert alert-warning">Please note that email subscriptions are not currently active for the forums.</div>-->
            <div class="row">
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header">
                            Forum Tools
                        </div>
                        <div class="card-body">
                            <!--
                            <input type="checkbox" [[subscribed ? 'checked' : '']] on-click="subscribe"> Forum Subscription <span class="glyphicon glyphicon-question-sign" data-toggle='tooltip' title='Receive email updates when new topics are posted.'></span>
                            <br>
                            <input type="checkbox" [[subscribed ? '' : 'disabled' ]] [[subscribed ? autosubscribed ? 'checked' : '' : '']] on-click='autosubscribe'> Auto Topic Subscription <span class="glyphicon glyphicon-question-sign" data-toggle='tooltip' title='Automatically subscribe to all new topics. When enabled, you will get emails for responses to topics, not just new topics.'></span>
                            <div class="btn btn-outline-dark" on-click="updateSubscription">[[update]]</div>
                            -->
                            <a class="btn btn-primary btn-sm mb-2" href="create">Create New Discussion Topic</a>
                            <br/>
                            Sort by:
                            <button class="btn btn-sm btn-outline-dark mt-1" on-click="['sort','created_at',false]">Newest post</button>
                            <button class="btn btn-sm btn-outline-dark mt-1" on-click="['sort','created_at',true]">Oldest post</button>
                            <button class="btn btn-sm btn-outline-dark mt-1" on-click="['sort','updated_at',false]">Recent activity</button>
                        </div>
                        <div class="card-footer">
                            <div class="badge badge-primary">[[newActivity]]</div> topics with activity you have not viewed.
                            <button class="btn btn-sm [[filtered_new ? 'btn-primary' : 'btn-outline-primary']]" on-click="@.toggle('filtered_new')">[[filtered_new ? 'Show All' : 'Filter']]</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card h-100">
                        <div class="card-header">
                            Your Forum Participation Stats
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    <span class="badge badge-pill badge-secondary">[[stats.posts]]</span> Topics posted
                                </div>
                                <div class="col-sm-6">
                                    <span class="badge badge-pill badge-secondary">[[stats.responses]]</span> Responses posted
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <span class="badge badge-pill badge-secondary">[[stats.yourVotes]]</span> Responses you marked helpful
                                </div>
                                <div class="col-sm-6">
                                    <span class="badge badge-pill badge-secondary">[[stats.helpfulAnswers]]</span> Your responses marked helpful
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <span class="badge badge-pill badge-secondary">[[stats.helpfulVotes]]</span> Helpful votes received
                                </div>
                                <div class="col-sm-6">
                                    <span class="badge badge-pill badge-secondary">[[stats.endorsed]]</span> Responses endorsed by instructor
                                </div>
                            </div>
                        </div>

                        @if($instructor)
                            <div class="card-footer">
                                <a class="btn btn-outline-dark" href="stats">Class Stats</a>
                            </div>
                        @else
                            <div class="card-footer">
                                <span data-toggle='tooltip' title='Earn badges for participationg in the forum.  Icons made by Freepik (freepik.com) from www.flaticon.com'>Participation Badges:</span>
                                [[#if stats.views > 5]]
                                <img id="participation_badge" width="40" height ="40" src="/img/badges/051-secure.png" alt="participation_badge" data-toggle='tooltip' title='Earned for viewing five posts!'>
                                [[elseif stats.views > 0]]
                                <img id="participation_badge" width="40" height ="40" src="/img/badges/protection-glasses.png" alt="participation_badge" data-toggle='tooltip' title='Earned for viewing your first post!'>
                                [[/if]]
                                [[#if stats.posts > 5]]
                                <img id="participation_badge" width="40" height ="40" src="/img/badges/051-beaker.png" alt="participation_badge" data-toggle='tooltip' title='Earned for posting five topics!'>
                                [[elseif stats.posts > 0]]
                                <img id="participation_badge" width="40" height ="40" src="/img/badges/beaker.png" alt="participation_badge" data-toggle='tooltip' title='Earned for posting your first topic!'>
                                [[/if]]
                                [[#if stats.responses > 5]]
                                <img id="participation_badge" width="40" height ="40" src="/img/badges/051-laboratory-3.png" alt="participation_badge" data-toggle='tooltip' title='Earned for posting five responses!'>
                                [[elseif stats.responses > 0]]
                                <img id="participation_badge" width="40" height ="40" src="/img/badges/erlenmeyer-flask.png" alt="participation_badge" data-toggle='tooltip' title='Earned for posting your first response!'>
                                [[/if]]
                                [[#if stats.yourVotes > 5]]
                                <img id="participation_badge" width="40" height ="40" src="/img/badges/051-lab-4.png" alt="participation_badge" data-toggle='tooltip' title='Earned for making five hepful votes!'>
                                [[elseif stats.yourVotes > 0]]
                                <img id="participation_badge" width="40" height ="40" src="/img/badges/centrifuge-tube.png" alt="participation_badge" data-toggle='tooltip' title='Earned for making your first helpful vote!'>
                                [[/if]]
                                [[#if stats.helpfulAnswers > 5]]
                                <img id="participation_badge" width="40" height ="40" src="/img/badges/051-lab-23.png" alt="participation_badge" data-toggle='tooltip' title='Earned for having five posts marked helpful!'>
                                [[elseif stats.helpfulAnswers > 0]]
                                <img id="participation_badge" width="40" height ="40" src="/img/badges/bunsen-burner.png" alt="participation_badge" data-toggle='tooltip' title='Earned for having your first answer marked helpful!'>
                                [[/if]]
                                [[#if stats.helpfulVotes > 5]]
                                <img id="participation_badge" width="40" height ="40" src="/img/badges/051-test-tube.png" alt="participation_badge" data-toggle='tooltip' title='Earned for receiving five helpful votes!'>
                                [[elseif stats.helpfulVotes > 0]]
                                <img id="participation_badge" width="40" height ="40" src="/img/badges/test-tube-rack.png" alt="participation_badge" data-toggle='tooltip' title='Earned for receiving your first helpful vote!'>
                                [[/if]]
                                [[#if stats.endorsed > 5]]
                                <img id="participation_badge" width="40" height ="40" src="/img/badges/051-clothing.png" alt="participation_badge" data-toggle='tooltip' title='Earned for receiving five instructor-endorsed answers!'>
                                [[elseif stats.endorsed > 0]]
                                <img id="participation_badge" width="40" height ="40" src="/img/badges/lab-coat.png" alt="participation_badge" data-toggle='tooltip' title='Earned for receiving your first instructor-endorsed answer!'>
                                [[/if]]
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>

        [[#forums:f]]
        [[#if !(~/filtered_new && !new_activity)]]
        <a href="view/[[id]]" class="card mt-1 [[viewed > 0 ? 'bg-light' : 'text-white bg-info']]">
            <div class="card-header">[[title]]</div>
            <div class="card-body">[[preview]]</div>
            <div class="card-footer">
                Responses <span class="badge badge-pill badge-secondary">[[forum_answers_count]]</span>
                Viewers <span class="badge badge-pill badge-secondary">[[viewers_count]]</span>
                <span class="float-right">Last activity [[updated_at]]</span>
                [[#if new_activity && viewed]]
                <button type="button" class="btn btn-primary btn-sm" data-toggle='tooltip' title='New responses or other updates to this topic since you last viewed it.'>
                    New updates!
                </button>
                [[/if]]
            </div>
        </a>
        [[/if]]
        [[/forums]]

</div>

