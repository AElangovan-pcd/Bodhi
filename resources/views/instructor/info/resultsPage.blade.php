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

            <h3>Information Quiz Results Page</h3>
                <div class="btn btn-sm btn-outline-dark" on-click="csv">Download</div>
                <a href="../regrade/[[quiz.id]]" class="btn btn-sm btn-outline-info">Regrade</a>
                <div class="btn btn-sm btn-outline-secondary" on-click="@this.toggle('showAnswers')">[[showAnswers ? 'Show Points' : 'Show Selections']]</div>
                [[#showAnswers]]<div class="alert alert-info">Currently shows the index of the selected choice (starting at 0).</div>[[/showAnswers]]
            <table class="table">
                <tr>
                    <th on-click="sort" id="firstname">First Name</th>
                    <th on-click="sort" id="lastname">Last Name</th>
                    <th on-click="sort" id="seat">Seat</th>
                    <th on-click="sort" id="deadline">Lead Time
                        <i class="far fa-question-circle" aria-hidden data-toggle="tooltip" data-placement="right" title="Number of hours submitted prior to deadline (based on first submit click)."></i>
                        <span class="sr-only">Number of hours submitted prior to deadline (based on first submit click).</span>
                    </th>
                    <th on-click="sort" id="total">Total</th>
                    [[#quiz.info_quiz_questions:q]]
                    <th on-click="sort" id="[[q]]">[[q+1]]</th>
                    [[/quiz.info_quiz_questions]]
                </tr>
                [[#rows:r]]
                <tr>
                    <td>[[firstname]]</td>
                    <td>[[lastname]]</td>
                    <td>[[seat]]</td>
                    <td>[[deadline]]</td>
                    <td>[[total]]</td>
                    [[#answers]]
                    <td>[[~/showAnswers ? answer.selected : earned]]</td>
                    [[/answers]]
                </tr>
                [[/rows]]
            </table>

        </div>
    </div>
</div>
