@extends('layouts.instructor')

@section('links')
    <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
@stop

@section('JS')
    <script src="{{ asset('js/ractive.min.js') }}"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.9/flatpickr.min.css" rel="stylesheet" media="all">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.9/flatpickr.min.js"></script>
    <script src='{{ asset('/js/moment.min.js') }}'></script>
    <link href="{{ asset('css/summernote/summernote-bs4.css') }}" rel="stylesheet">
    <script src="{{ asset('js/summernote-bs4.js') }}"></script>
    <script src="{{ asset('js/exif.js') }}"></script>
    <script src="{{ asset('js/summernote-images.js') }}"></script>
@endsection

@section('content')

    <div id="target"></div>

    <script id="template" type="text/ractive">
        @include('instructor.scheduleLandingPage')
    </script>

    <script type="text/javascript">
        const saveURL     = "{{url('/instructor/course/'.$course->id.'/info/saveInfo')}}";

        var data = {!! $data !!};

        data.getAssignmentIndex = function(id) {
            return data.assignments.findIndex(x => parseInt(x.id) == parseInt(id));
        };

        console.log(data);

        var ractive = new Ractive({
            el: "#target",
            template: "#template",
            data: data,
            delimiters: [ '[[', ']]' ],
        });

        ractive.set("save_sch_msg","Save Schedule");

        // Schedules
        var course = ractive.get("course");
        var assignments = ractive.get("assignments");
        var schedules = ractive.get("schedules");

        function unsavedSchedules(state) {
            ractive.set("unsavedSchedules", state);
            ractive.set("save_sch_msg", "Save Schedule");
        }

        var calendars = flatpickr("input[data-id='schedule_cal'",
            {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                altInput: true,
                altInputClass: "inline-block",
                altFormat: "F j, Y H:i",
            }
        );

        if(calendars.length==undefined) {  //If there's only one calendar, it's not an array
            calendars.setDate(schedules[0].time);
            calendars.config.onChange.push(function(selectedDates, dateStr, instance) {
                var index = instance.input.name;
                schedules[index].time=dateStr;
                unsavedSchedules(true);
            });
        }
        //Initialize existing schedules
        for(i=0; i<calendars.length; i++) {
            calendars[i].setDate(schedules[i].time);
            calendars[i].config.onChange.push(function(selectedDates, dateStr, instance) {
                var index = instance.input.name;
                schedules[index].time=dateStr;
                unsavedSchedules(true);
            });
        }

        ractive.on("add_schedule", function(event) {
            var course = ractive.get("course");
            var assignments = ractive.get("assignments");
            var schedules = ractive.get("schedules");
            var new_schedule = {
                id : -1,
                type: "assignment",
                course_id: course.id,
                details: {
                    assignment_id: assignments[0].id,
                    property: "Active",
                    state: 1,
                },
                completed: 0,
                enabled: 1,
                time: moment(moment.now()).format("YYYY-MM-DD H:mm"),
            };
            unsavedSchedules(true);
            schedules.push(new_schedule);
            ractive.update();
            var loc = schedules.length-1;
            var pickr = flatpickr("#time"+loc,
                {
                    enableTime: true,
                    defaultDate: new_schedule.time,
                    dateFormat: "Y-m-d H:i",
                    altInput: true,
                    altInputClass: "inline-block",
                    altFormat: "F j, Y H:i",
                    onChange: function(selectedDates, dateStr, instance) {
                        console.log(course.id+ " " + loc + " change date to " + dateStr);
                        schedules[loc].time=dateStr;
                    }
                });
            console.log(pickr);
        });

        ractive.on("remove_schedule", function(event) {
            var sched = event.get();
            sched.deleted=true;
            unsavedSchedules(true);
            //Don't actually delete the item from the array to avoid problems with the onchange event for the picker.  Mark it deleted so it can be removed from the database.
            ractive.update();
        });

        ractive.on("re-enable_schedule", function(event) {
            var sched = event.get();
            sched.completed=0;
            sched.enabled=1;
            unsavedSchedules(true);
            ractive.update();
        });

        ractive.on("schedule_assignment", function(event) {
            var sched = event.getParent().getParent().get();
            sched.details.assignment_id = event.node.id;
            console.log(sched);
            unsavedSchedules(true);
            ractive.update();
        });

        ractive.on("schedule_property", function(event) {
            var sched = event.get();
            sched.details.property = event.node.id;
            console.log(sched);
            unsavedSchedules(true);
            ractive.update();
        });

        ractive.on("schedule_state", function(event) {
            var sched = event.get();
            sched.details.state = (event.node.id === 'true' ? 1 : 0 );
            console.log(sched);
            unsavedSchedules(true);
            ractive.update();
        });

        ractive.on("schedule_linked", function(event) {
            var sched = event.get();
            sched.details.linked = (event.node.id === 'true' ? 1 : 0 );
            console.log(sched);
            unsavedSchedules(true);
            ractive.update();
        });

        ractive.on("save_schedule", function(event) {
            ractive.set("save_sch_msg","Saving...");
            var schedules = ractive.get("schedules");
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
                    ractive.set("schedules",response);
                    ractive.set("save_sch_msg","Saved");
                    ractive.set("unsavedSchedules",false);
                    ractive.update();
                })
                .fail(function(xhr,status,error) {  //Deal with post failure.
                    ractive.set("save_sch_msg","Error Saving");
                    console.log(error);
                });
        });


    </script>
@stop
