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
                    Options
                </div>
                <div class="card-body">
                    Filter:
                    <div class="btn btn-outline-success btn-sm" on-click="filter" id="0">Active</div>
                    <div class="btn btn-outline-danger btn-sm" on-click="filter" id="1">Inactive</div>
                    <div class="btn btn-outline-dark btn-sm" on-click="filter" id="2">All</div>
                </div>
            </div>
            <div class="card mt-2">
                <div class="card-header">
                    Course List
                </div>
                <div class="card-body">
                    <table class="table table-responsive-sm">
                        <tr>
                            <th on-click="@.sort('owner')">Owner</th>
                            <th on-click="@.sort('name')">Course Name</th>
                            <th on-click="@.sort('active')">Status</th>
                            <th on-click="@.sort('created_at')">Created</th>
                            <th>Action</th>
                        </tr>
                        [[#courses:c]]
                        [[#active != ~/filter]]
                        <tr class="[[active === 1 ? 'table-success' : 'table-danger']]">
                            <td>[[owner.firstname]] [[owner.lastname]]</td>
                            <td><a href="/instructor/course/[[id]]/landing">[[name]]</a></td>
                            <td>[[active === 1 ? 'Active' : 'Inactive']]</td>
                            <td>[[created_at]]</td>
                            [[#active === 1]]
                            <td><a href="{{url('/instructor/course/[[id]]/deactivate')}}" class="btn btn-danger btn-sm">Deactivate</a></td>
                            [[else]]
                            <td><a href="{{url('/instructor/course/[[id]]/activate')}}" class="btn btn-success btn-sm">Activate</a></td>
                            [[/active]]
                        </tr>
                        [[/active]]
                        [[/courses]]
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
