<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
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
            <h3>Student Details</h3>
            <div class="card">
                <div class="card-header">
                    [[student.firstname]] [[student.lastname]] ([[student.email]])
                </div>
                <div class="card-body">
                    <form action="changeSeat" method="post">
                        @csrf
                        <div class="form-row align-items-center">
                            <div class="col-md-6 my-1">
                                <label class="sr-only" for="seat">Seat</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">Seat</div>
                                    </div>
                                    <input type="text" name="seat" class="form-control" id="seat" value="[[student.courses[0].pivot.seat]]">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="submit" action="submit">Save</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <form action="changeMultiplier" method="post">
                        @csrf
                        <div class="form-row align-items-center">
                            <div class="col-md-6 my-1">
                                <label class="sr-only" for="multiplier">Quiz Multiplier. Multiplier will be applied to time allotted for all quizzes when quiz jobs are generated.</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">Quiz Multiplier&nbsp
                                            <i class="far fa-question-circle" aria-hidden data-toggle="tooltip" data-placement="right" title="Multiplier will be applied to time allotted for all quizzes when quiz jobs are generated."></i>
                                        </div>
                                    </div>
                                    <input type="text" name="multiplier" class="form-control" id="multiplier" value="[[student.courses[0].pivot.multiplier === null ? '1' : student.courses[0].pivot.multiplier]]" twoway="false">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="submit" action="submit">Save</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <a href="dropStudent" class="btn btn-outline-dark mt-3" role="button">Drop Student</a>
                    <a href="resetPassword" class="btn btn-outline-dark mt-3" role="button">Reset Student Password</a>

                </div>
            </div>
        </div>
    </div>
</div>
