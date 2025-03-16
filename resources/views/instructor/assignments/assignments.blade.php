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
        @include('instructor.assignments.assignmentsPage')
    </script>

    <script type="text/javascript">
        var data = {!! $data !!};

        console.log(data);

        var ractive = new Ractive({
            el: "#target",
            template: "#template",
            data: data,
            delimiters: [ '[[', ']]' ],
        });

        ractive.set("save_sch_msg","Save");
        ractive.set("action", "Shift");

        // Schedules
        var course = ractive.get("course");
        var assignments = ractive.get("assignments");
        var schedules = ractive.get("schedules");

        function unsavedSchedules(state) {
            ractive.set("unsavedSchedules", state);
            ractive.set("save_sch_msg", "Save");
        }

        var cals = flatpickr("input[data-id='schedule_cal'",
            {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                altInput: true,
                altInputClass: "inline-block",
                altFormat: "F j, Y H:i",
            }
        );

        //This block is because the flatpickr initialization will create either a variable or an array
        var calendars = []
        if(cals.length===undefined) {
            calendars[0] = cals;
        }
        else
            calendars = cals;

        //Initialize existing schedules
        for(i=0; i<calendars.length; i++) {
            calendars[i].setDate(assignments[i].closes_at);
            calendars[i].config.onChange.push(function(selectedDates, dateStr, instance) {
                var index = instance.input.name;
                assignments[index].closes_at=dateStr;
                unsavedSchedules(true);
            });
        }

        ractive.on("clear_closes_at", function(context, index) {
            let assignments = ractive.get("assignments");
            assignments[index].closes_at = null;
            ractive.set("assignments", assignments);
            calendars[index].setDate(null);
        })

        ractive.on("select_all", function(context, state) {
            let assignments = ractive.get("assignments");
            assignments.forEach(a=> a.selected = state);
            ractive.set("assignments", assignments);
        });

        ractive.on("adjust", function(context, type) {
            let action = ractive.get("action");
            console.log(action)
            if(action === "Shift")
                shift(type);
            else if (action === "Set")
                set(type);
        }) ;

        function shift(type) {
            let assignments = ractive.get("assignments");
            assignments.forEach((a, i) => {
                if(a.selected === true && a.closes_at != null) {

                    let newDate = moment(a.closes_at).add(ractive.get("shiftNumber"), type).format("YYYY-MM-DD HH:mm");
                    console.log(newDate)
                    calendars[i].setDate(newDate);
                    a.closes_at = newDate;
                    unsaved()
                }
            })
        }

        function set(type) {
            let assignments = ractive.get("assignments");
            assignments.forEach((a, i) => {
                if(a.selected === true && a.closes_at != null) {
                    let newDate = moment(a.closes_at)
                    if(type === 'hours')
                        newDate = newDate.hour(ractive.get("shiftNumber")).format("YYYY-MM-DD HH:mm");
                    if(type === 'minutes')
                        newDate = newDate.minute(ractive.get("shiftNumber")).format("YYYY-MM-DD HH:mm");
                    console.log(newDate)
                    calendars[i].setDate(newDate);
                    a.closes_at = newDate;
                    unsaved()
                }
            })
        }

        function unsaved() {
            unsavedSchedules(true);
        };

        ractive.on("save_assignments", function(event) {
            ractive.set("save_sch_msg","Saving...");
            let assignments = ractive.get("assignments");

            $.post('saveAssignments',
                {
                    _token: "{{ csrf_token() }}",
                    assignments: JSON.stringify(assignments),
                })
                .done(function (response) {
                    //response = JSON.parse(response);
                    console.log(response);
                    if(response.status==="success") {
                        ractive.set("save_sch_msg", "Saved");
                        ractive.set("unsavedSchedules", false);
                    }
                    else
                        ractive.set("save_sch_msg", "Error Saving");
                    ractive.update();
                })
                .fail(function(xhr,status,error) {  //Deal with post failure.
                    ractive.set("save_sch_msg","Error Saving");
                    console.log(error);
                });
        });


    </script>
@stop

