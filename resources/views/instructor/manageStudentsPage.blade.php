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
            <div class="card">
                <div class="card-header">
                    Select Last Name
                </div>
                <div class="card-body">
                    <h3>
                        [[#alpha_section]]
                        <a on-click="section" class="mr-1" style="cursor: pointer">[[this]]</a>
                        [[/alpha_section]]
                    </h3>
                </div>
            </div>
            <div class="card mt-2">
                <div class="card-header">
                    Student List [[#if students.length>0]]([[students.length]] students)[[/if]]
                </div>
                <div class="card-body">
                    [[#loading]]
                    <div class="alert alert-warning">Loading students...</div>
                    [[else]]
                    <table class="table">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th></th>
                        </tr>
                        [[#students:s]]
                        <tr>
                            <td>[[firstname]] [[lastname]]</td>
                            <td>[[email]]</td>
                            <td><a href="manageStudents/resetPassword/[[id]]" class="btn btn-sm btn-outline-dark" role="button">Reset Password</a></td>
                        </tr>
                        [[/students]]
                    </table>
                    [[/loading]]
                </div>
            </div>
        </div>
    </div>
</div>
