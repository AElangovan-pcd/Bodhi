@extends('layouts.instructor')

@section('links')
    <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
    <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/review/landing')}}">Peer Review</a></li>
@stop

@section('JS')
    <script src="{{ asset('js/ractive.min.js') }}"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src='{{ asset('/js/moment.min.js') }}'></script>
@endsection

@section('content')

    <div id="target"></div>

    <script id="template" type="text/ractive">
        @include('instructor.review.monitorPage')
    </script>

    <script type="text/javascript">

        var data = {!! $data !!};
        data.range = function ( low, high ) {
            var range = [];
            for ( i = low; i <= high; i += 1 ) {
                range.push( i );
            }
            return range;
        }
        console.log(data);

        var ractive = new Ractive({
            el: "#target",
            template: "#template",
            data: data,
            delimiters: [ '[[', ']]' ],
        });

        var assignment = ractive.get("assignment");
        ractive.set("save_sch_msg","Save Schedule");

        var calendars = flatpickr(".flatpickr",
            {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                altInput: true,
                altInputClass: "inline-block",
                altFormat: "F j, Y H:i",
            }
        );

        if(calendars.length==undefined) {  //If there's only one calendar, it's not an array
            calendars.setDate(assignment.schedules[0].time);
            calendars.config.onChange.push(function(selectedDates, dateStr, instance) {
                var index = instance.input.name;
                assignment.schedules[index].time=dateStr;
                ractive.set("unsavedSchedules", true);
            });
        }
        //Initialize existing schedules
        for(i=0; i<calendars.length; i++) {
            calendars[i].setDate(assignment.schedules[i].time);
            calendars[i].config.onChange.push(function(selectedDates, dateStr, instance) {
                var index = instance.input.name;
                assignment.schedules[index].time=dateStr;
                ractive.set("unsavedSchedules", true);
            });
        }

        ractive.on("add_schedule", function(event) {
            var assignment = ractive.get("assignment");
            var new_schedule = {
                id : -1,
                review_assignment_id : assignment.id,
                state : assignment.state+1,
                time: moment(moment.now()).format("YYYY-MM-DD H:mm"),
                reviewNum: 2,
            };
            ractive.set("unsavedSchedules", true);
            assignment.schedules.push(new_schedule);
            ractive.update();
            var loc = assignment.schedules.length-1;
            flatpickr("#time"+loc,
                {
                    enableTime: true,
                    defaultDate: new_schedule.time,
                    dateFormat: "Y-m-d H:i",
                    altInput: true,
                    altInputClass: "inline-block",
                    altFormat: "F j, Y H:i",
                    onChange: function(selectedDates, dateStr, instance) {
                        console.log(assignment.id+ " " + loc + " change date to " + dateStr);
                        assignment.schedules[loc].time=dateStr;
                    }
                });
        });

        ractive.on("remove_schedule", function(event) {
            var sched = event.get();
            sched.deleted=true;
            //Don't actually delete the item from the array to avoid problems with the onchange event for the picker.  Mark it deleted so it can be removed from the database.
            ractive.update();
        });

        ractive.on("schedule_state", function(event) {
            var sched = event.getParent().getParent().get();
            sched.state = event.node.id;
            console.log(sched);
            ractive.set("unsavedSchedules", true);
            ractive.update();
        });

        ractive.on("save_schedule", function(event) {
            ractive.set("save_sch_msg","Saving...");
            var assignment = ractive.get("assignment");
            var schedules = assignment.schedules;
            console.log(schedules);
            if(schedules.length === 0) {
                console.log("no events");
                return;
            }

            $.post('saveSchedules',
                {
                    _token: "{{ csrf_token() }}",
                    schedules: schedules,
                })
                .done(function (response) {
                    response = JSON.parse(response);
                    console.log(response);
                    location.reload();
                })
                .fail(function(xhr,status,error) {  //Deal with post failure.  Suggest page reload in case it's a session timeout.
                    ractive.set("save_sch_msg","Error Saving");
                    console.log(error);
                });
        });

        ractive.on("set_type", function(event) {
            ractive.set("type",event.node.id);
        });

        ractive.on("allow_delete", function(event) {
            students = ractive.get("assignment.students");
            students[event.node.id].allow_delete = true;
            ractive.update();
        });

        ractive.on("clear_delete", function(event) {
            students = ractive.get("assignment.students");
            students[event.node.id].allow_delete = false;
            ractive.update();
        });

        //Include the name in the file upload dialog
        $('#feedback_import').on('change',function(){
            //get the file name
            var fileName = $(this).val();
            fileName = fileName.replace(/^.*\\/, "");
            //replace the "Choose a file" label
            $(this).next('.custom-file-label').html(fileName);
            console.log(fileName);
        });

        //Include the name in the file upload dialog
        $('#assignment_import').on('change',function(){
            //get the file name
            var fileName = $(this).val();
            fileName = fileName.replace(/^.*\\/, "");
            //replace the "Choose a file" label
            $(this).next('.custom-file-label').html(fileName);
            console.log(fileName);
        });

    </script>
@stop
