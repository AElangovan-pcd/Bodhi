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

            <h3>Assignment List for [[course.name]]</h3>

            <div class="card-deck mb-2">
                <div class="card">
                    <div class="card-header">
                        Assignments
                    </div>
                    <div class="card-body">
                        <button class="btn btn-info" on-click="['select_all', true]">Select All</button>
                        <button class="btn btn-outline-dark" on-click="['select_all', false]">Unselect All</button>
                        <div class="form-check-inline mb-2">
                            <div class="input-group ml-2">
                                <label class="sr-only" for="action">Action</label>
                                <select class="custom-select mr-sm-2" id="action" value="[[action]]">
                                    <option value="Shift" selected>Shift</option>
                                    <option value="Set">Set</option>
                                </select>
                                <div class="input-group-prepend">
                                    <div class="input-group-text">[[action === "Shift" ? 'by' : 'to']]</div>
                                </div>
                                <label class="sr-only" for="shiftNumber">Shift Number</label>
                                <input type="number" class="form-control" id="shiftNumber" placeholder="Number"value="[[shiftNumber]]">

                            </div>

                        </div>
                        [[#action === "Shift"]]<button class="btn btn-outline-dark" on-click="['adjust','days']">Days</button>[[/action]]
                        <button class="btn btn-outline-dark" on-click="['adjust','hours']">Hours</button>
                        <button class="btn btn-outline-dark" on-click="['adjust','minutes']">Minutes</button>
                        <div class="btn btn-info ml-2" on-click="save_assignments">[[save_sch_msg]]</div>
                        [[#unsavedSchedules]]
                        <div class="badge badge-warning">Unsaved Changes</div>
                        [[/unsavedSchedules]]

                        <ul class="list-group mb-1">
                            [[#assignments:a]]
                            <li class="list-group-item py-0">
                                <div class="row ">
                                    <div class="col-md-5 mx-2 px-0 d-flex">
                                        [[#selected]]
                                        <button class="btn btn-sm btn-info mr-0" on-click="@.toggle('assignments['+a+'].selected')"><svg aria-labelledby="select-[[a]]" role="img" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><title id="select-[[a]]">Select</title><!--! Font Awesome Free 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M64 80c-8.8 0-16 7.2-16 16V416c0 8.8 7.2 16 16 16H384c8.8 0 16-7.2 16-16V96c0-8.8-7.2-16-16-16H64zM0 96C0 60.7 28.7 32 64 32H384c35.3 0 64 28.7 64 64V416c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V96zM337 209L209 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L303 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"/></svg></button>
                                        [[else]]
                                        <button class="btn btn-sm btn-outline-dark mr-0" on-click="@.toggle('assignments['+a+'].selected')"><svg aria-labelledby="unselect-[[a]]" role="img" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><title id="unselect-[[a]]">Unselect</title><!--! Font Awesome Free 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M384 80c8.8 0 16 7.2 16 16V416c0 8.8-7.2 16-16 16H64c-8.8 0-16-7.2-16-16V96c0-8.8 7.2-16 16-16H384zM64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64z"/></svg></button>
                                        [[/selected]]
                                        <input type="text" class="form-control d-inline-block" id="assignment_name" placeholder="Assignment name" aria-label="Assignment name" value="[[name]]" onkeydown="unsaved()">
                                    </div>
                                    <div class="col-md-6 my-auto">
                                        Closes:
                                        <input id="closes_at" class="flatpickr" data-id="schedule_cal" name="[[a]]" type="text" placeholder="No End Date...">
                                        <button class="btn btn-sm btn-outline-dark" on-click="['clear_closes_at',a]">Clear</button>
                                    </div>
                                </div>
                            </li>
                            [[/assignments]]
                        </ul>
                        [[#unsavedSchedules]]
                        <div class="alert alert-warning">You have unsaved changes.  Reload to abandon changes.</div>
                        [[/unsavedSchedules]]
                        <div class="btn btn-sm btn-info" on-click="save_assignments">[[save_sch_msg]]</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
