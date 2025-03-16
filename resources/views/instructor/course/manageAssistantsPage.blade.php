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
            <div class="alert alert-warning" role="alert">
                This is a new feature in development.  Currently, students with LA access can view results for assignments in this course only, but cannot manage anything else.  Some features may not yet work correctly.
            </div>
            <div class="card-deck">
                <div class="card">
                    <div class="card-header">
                        Add Learning Assistant
                    </div>
                    <div class="card-body">
                        <form action="{{url('/instructor/course/'.$course->id.'/addAssistant')}}" method="post">
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
                <div class="card">
                    <div class="card-header">Assistant Settings</div>
                    <div class="card-body">
                        Your Learning Assistants <strong>[[course.assistant_privs.edit == true ? 'can' : 'cannot']]</strong> access the assignment editor.
                        <a href="toggleAssistantEdit" class="btn btn-outline-dark btn-sm">Toggle Setting</a>
                    </div>
                </div>
            </div>
            <div class="card mt-2">
                <div class="card-header">
                    Learning Assistant List
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th></th>
                        </tr>
                        [[#assistants:a]]
                        <tr>
                            <td>[[firstname]] [[lastname]]</td>
                            <td>[[email]]</td>
                            <td><a href="{{url('/instructor/course/'.$course->id.'/revokeAssistant/[[id]]')}}" class="btn btn-danger btn-sm">Revoke</a></td>
                        </tr>
                        [[/assistants]]
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
