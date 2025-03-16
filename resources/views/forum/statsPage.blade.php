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
                <th on-click="sort" id="views">Topics Viewed</th>
                <th on-click="sort" id="posts">Topics Posted</th>
                <th on-click="sort" id="responses">Responses Posted</th>
                <th on-click="sort" id="yourVotes">Helpful Votes Made</th>
                <th on-click="sort" id="helpfulAnswers">Responses Voted Helpful</th>
                <th on-click="sort" id="helpfulVotes">Helpful Votes Received</th>
                <th on-click="sort" id="endorsed">Instructor Endorsements</th>
                </thead>
                <tbody>
                [[#users:u]]
                <tr>
                    <td>[[firstname]]</td>
                    <td>[[lastname]]</td>
                    <td>[[seat]]</td>
                    <td>[[views]]</td>
                    <td>[[posts]]</td>
                    <td>[[responses]]</td>
                    <td>[[yourVotes]]</td>
                    <td>[[helpfulAnswers]]</td>
                    <td>[[helpfulVotes]]</td>
                    <td>[[endorsed]]</td>
                </tr>
                [[/users]]
                </tbody>
            </table>
        </div>
    </div>
</div>