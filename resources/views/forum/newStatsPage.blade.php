<div class="container">
    <div class="row">
        <div class="col-md-12">
            @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
            @endif

            <button id="tsv_button" class="btn btn-default" on-click="tsv">
                TSV <span class="glyphicon glyphicon-copy"></span>
            </button>
            <table id="stats_table" class="table table-striped fade-in fade-out">
                <thead>
                <th on-click="sort" id="firstname">First Name</th>
                <th on-click="sort" id="lastname">Last Name</th>
                <th on-click="sort" id="seat">Seat</th>
                <th on-click="sort" id="forum_views_count">Topics Viewed</th>
                <th on-click="sort" id="forums_count">Topics Posted</th>
                <th on-click="sort" id="forum_answers_count">Responses Posted</th>
                <th on-click="sort" id="forum_votes_count">Helpful Votes Made</th>
                <th on-click="sort" id="forum_helpful_answers_count">Responses Voted Helpful</th>
                <th on-click="sort" id="forum_helpful_votes_count">Helpful Votes Received</th>
                <th on-click="sort" id="forum_endorsed_answers_count">Instructor Endorsements</th>
                </thead>
                <tbody>
                [[#users:u]]
                <tr>
                    <td>[[firstname]]</td>
                    <td>[[lastname]]</td>
                    <td>[[seat]]</td>
                    <td>[[forum_views_count]]</td>
                    <td>[[forums_count]]</td>
                    <td>[[forum_answers_count]]</td>
                    <td>[[forum_votes_count]]</td>
                    <td>[[forum_helpful_answers_count]]</td>
                    <td>[[forum_helpful_votes_count]]</td>
                    <td>[[forum_endorsed_answers_count]]</td>
                </tr>
                [[/users]]
                </tbody>
            </table>
        </div>
    </div>
</div>
