<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif


            <div class="card-deck">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">Layout</div>
                    <div class="card-body">
                        <canvas id="classCanvas"></canvas>
                        <button type="button" class="btn btn-outline-dark btn-sm mb-1" data-toggle="modal" data-target="#uploadModal">
                            Select Classroom Image
                        </button>
                        <button type="button" class="btn btn-outline-dark btn-sm mb-1" data-toggle="modal" data-target="#importModal">
                            Import Seat Layout
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-dark mb-1" on-click="export">Export Seat Layout</button>
                        <button type="button" class="btn btn-sm btn-info mb-1" on-click="save">[[save_msg]]</button>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">Seats</div>
                    <div class="card-body" id="seatList" style="overflow-y:scroll; max-height:75vh">
                        <ul class="list-group">
                            [[#seats:s]]
                            <li class="list-group-item d-flex justify-content-between align-items-center" class-active="~/selectedSeat === s" id="[[s]]" on-click="select-seat">
                                <input class="form-control col-3" type="text" placeholder="Seat" value="[[name]]">
                                [[#if !x>0]]Needs position[[/if]]
                                <button class="btn btn-sm btn-danger" on-click="delete" id="[[s]]"><i class="far fa-trash-alt" aria-hidden></i><span class="sr-only">Delete</span></button>
                            </li>
                            [[/seats]]
                        </ul>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-sm btn-success mt-2 mr-auto" on-click="add"><i class="fas fa-plus-circle" aria-hidden></i> Add Seat</button>
                            <button type="button" class="btn btn-sm btn-info mt-2 ml-auto" on-click="save">[[save_msg]]</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('instructor.course.classroomLayoutModals')
