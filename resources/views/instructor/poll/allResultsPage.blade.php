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

            <h3>All Poll Results for [[course.name]]</h3>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Polls Answered</th>
                            [[#course.polls]]
                            <th>[[name]]</th>
                            [[/course.polls]]
                        </tr>
                        </thead>
                        <tbody>
                        [[#results_table]]
                        <tr>
                            <td>[[firstname]]&nbsp[[lastname]]</td>
                            <td>[[poll_responses]]</td>
                            [[#poll_items]]
                            <td>[[& answer]]</td>
                            [[/poll_items]]
                        </tr>
                        [[/results_table]]
                        </tbody>
                    </table>
                </div>

        </div>
    </div>
</div>

