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

                <!--
            <div class="alert alert-info" role="alert">
                New feature (Mar 17, 2019): Assignment inline-chat.
                <div class="btn btn-sm btn-outline-info" data-toggle="modal" data-target="#learnModal">Learn more</div>
            </div> -->

            <div class="card-deck">
                @if ($user->admin)
                    <div class="card">
                        <div class="card-header">Admin Tools</div>
                        <div class="card-body">
                            <a href="{{url('admin/manageInstructors')}}" role="button" class="btn btn-light btn-sm mb-1">
                                Manage Instructors
                            </a>
                            <a href="{{url('admin/manageCourses')}}" role="button" class="btn btn-light btn-sm mb-1">
                                Manage Courses
                            </a>
                            <a href="{{url('admin/logs')}}" role="button" class="btn btn-light btn-sm mb-1">
                                Error Logs
                            </a>
                        </div>
                    </div>
                @endif
                <div class="card">
                    <div class="card-header">Tools</div>
                    <div class="card-body">
                        <a href="{{url('instructor/createCourse')}}" role="button" class="btn btn-light btn-sm mb-1">
                            Create Course
                        </a>
                        <a href="{{url('join')}}" role="button" class="btn btn-light btn-sm mb-1">
                            Join Course
                        </a>
                        <a href="{{url('changePassword')}}" role="button" class="btn btn-light btn-sm mb-1">
                            Change Password
                        </a>
                        <button type="button" class="btn btn-light btn-sm mb-1" data-toggle="modal" data-target="#nameModal">
                            Change Name
                        </button>
                        <a href="{{url('instructor/manageStudents')}}" role="button" class="btn btn-light btn-sm mb-1">
                            Manage Students
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-deck mt-3">
                <div class="card border-success">
                    <div class="card-header">Active Courses</div>
                    <div class="card-body">
                        <ul class="list-group flex-column">
                            [[#active:a]]
                            <a class="list-group-item d-flex justify-content-between align-items-center" href="{{url('instructor/course/[[id]]/landing')}}">
                                [[name]]
                                <div class="btn-group" role="group" aria-label="First group">
                                    [[#if owner != user_id]]
                                    <a href="{{url('course/[[id]]/drop')}}" role="button" class="btn btn-sm btn-light"><i class="fas fa-sign-out-alt" aria-hidden></i><span class="sr-only">Drop</span></a>
                                    [[/if]]
                                    <a href="{{url('instructor/course/[[id]]/duplicate')}}" role="button" class="btn btn-sm btn-dark"><i class="far fa-copy" aria-hidden></i><span class="sr-only">Duplicate</span></a>
                                    <a href="{{url('instructor/course/[[id]]/deactivate')}}" role="button" class="btn btn-sm btn-danger"><i class="far fa-times-circle" aria-hidden></i><span class="sr-only">Deactivate</span></a>
                                </div>
                            </a>
                            [[/active]]
                        </ul>
                    </div>
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <span>Mouseover for key:</span>
                        <div class="btn-group"  role="group" aria-label="First group">
                            <button type="button" class="btn btn-sm btn-light" data-toggle="tooltip" data-placement="top" title="Drop"><i class="fas fa-sign-out-alt" aria-hidden></i><span class="sr-only">Drop</span></button>
                            <button type="button" class="btn btn-sm btn-dark" data-toggle="tooltip" data-placement="top" title="Duplicate"><i class="far fa-copy" aria-hidden></i><span class="sr-only">Duplicate</span></button>
                            <button type="button" class="btn btn-sm btn-danger" data-toggle="tooltip" data-placement="top" title="Deactivate"><i class="far fa-times-circle" aria-hidden></i><span class="sr-only">Deactivate</span></button>
                        </div>
                    </div>
                </div>
                <div class="card border-danger">
                    <div class="card-header">Inactive Courses</div>
                    <div class="card-body">
                        <ul class="list-group flex-column">
                            [[#inactive:i]]
                            [[#!archived || ~/showArchived]]
                            <a class="list-group-item d-flex justify-content-between align-items-center" href="{{url('instructor/course/[[id]]/landing')}}">
                                [[name]] [[archived ? '(Archived)' : '']]
                                <div class="btn-group" role="group" aria-label="First group">
                                    [[#if owner != user_id]]
                                    <a href="{{url('course/[[id]]/drop')}}" role="button" class="btn btn-sm btn-light"><i class="fas fa-sign-out-alt" aria-hidden></i><span class="sr-only">Drop</span></a>
                                    [[/if]]
                                    <a href="{{url('instructor/course/[[id]]/duplicate')}}" role="button" class="btn btn-sm btn-dark"><i class="far fa-copy" aria-hidden></i><span class="sr-only">Duplicate</span></a>
                                    [[#!archived]]
                                    <a href="{{url('instructor/course/[[id]]/activate')}}" role="button" class="btn btn-sm btn-success"><i class="fas fa-check" aria-hidden></i><span class="sr-only">Activate</span></a>
                                    <a href="{{url('instructor/course/[[id]]/archive')}}" role="button" class="btn btn-sm btn-info"><i class="fas fa-archive" aria-hidden></i><span class="sr-only">Archive</span></a>
                                    [[else]]
                                    <a href="{{url('instructor/course/[[id]]/unArchive')}}" role="button" class="btn btn-sm btn-info"><i class="fas fa-eye" aria-hidden></i><span class="sr-only">Unarchive</span></a>
                                    [[/archived]]
                                    <button onclick="return false;" class="btn btn-sm btn-danger" role="button" data-toggle="modal" data-target="#modal_remove_course_[[id]]"><i class="far fa-trash-alt" aria-hidden></i><span class="sr-only">Delete</span></button>
                                </div>
                            </a>
                            [[/archived]]
                            [[/inactive]]
                        </ul>
                        [[#archivedCount>0]]
                        <button class="btn btn-sm btn-info mt-2" on-click="@.toggle('showArchived')">[[showArchived ? "Hide" : "Show"]] [[archivedCount]] Archived Courses</button>
                        [[/archivedCount]]
                    </div>
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <span>Mouseover for key:</span>
                        <div class="btn-group"  role="group" aria-label="First group">
                            <button type="button" class="btn btn-sm btn-light" data-toggle="tooltip" data-placement="top" title="Drop"><i class="fas fa-sign-out-alt" aria-hidden></i><span class="sr-only">Drop</span></button>
                            <button type="button" class="btn btn-sm btn-dark" data-toggle="tooltip" data-placement="top" title="Duplicate"><i class="far fa-copy" aria-hidden></i><span class="sr-only">Duplicate</span></button>
                            <button type="button" class="btn btn-sm btn-success" data-toggle="tooltip" data-placement="top" title="Activate"><i class="fas fa-check" aria-hidden></i><span class="sr-only">Activate</span></button>
                            <button type="button" class="btn btn-sm btn-info" data-toggle="tooltip" data-placement="top" title="Archive (hide from list)"><i class="fas fa-archive" aria-hidden></i><span class="sr-only">Archive</span></button>
                            <button type="button" class="btn btn-sm btn-info" data-toggle="tooltip" data-placement="top" title="Unarchive"><i class="fas fa-eye" aria-hidden></i><span class="sr-only">Unarchive</span></button>
                            <button type="button" class="btn btn-sm btn-danger" data-toggle="tooltip" data-placement="top" title="Delete"><i class="far fa-trash-alt" aria-hidden></i><span class="sr-only">Delete</span></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mt-2">
                <div class="card-header">Welcome to LabPal</div>
                <div class="card-body">
                    <p>If you want to see what the student experience is like, please use the following temporary test accounts (username / password):</p>
                    <ul>
                        <li>student@calpoly.edu / df4TD</li>
                        <li>student2@calpoly.edu / ppQo923fkV%n</li>
                    </ul>
                </div>
                <div class="card-footer">Jun 27, 2019</div>
            </div>
        </div>
    </div>
</div>



@include('instructor.homeModals')
