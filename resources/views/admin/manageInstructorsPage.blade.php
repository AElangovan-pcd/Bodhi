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
                    Add Instructor
                </div>
                <div class="card-body">
                    <form action="{{url('/admin/addInstructor')}}" method="post">
                        @csrf
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">Email</span>
                            </div>
                            <input type="text" name="email" id="email">
                        </div>
                        <button action="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
            <div class="card mt-2">
                <div class="card-header">
                    Instructor List
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Admin?</th>
                            <th></th>
                        </tr>
                        [[#instructors:i]]
                        <tr>
                            <td>[[firstname]] [[lastname]]</td>
                            <td>[[email]]</td>
                            <td>[[admin === 1 ? 'Yes' : '']]</td>
                            <td><a href="{{url('/admin/revokeInstructor/[[id]]')}}" class="btn btn-danger btn-sm">Revoke</a></td>
                        </tr>
                        [[/instructors]]
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>