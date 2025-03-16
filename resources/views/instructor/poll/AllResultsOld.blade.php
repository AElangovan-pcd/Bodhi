@extends('layouts/LabPal')

@section('hands')
        <div id="hands" class="navbar-text"></div>
@stop
@section('links')
    {{ HTML::link('/professor/polls/'.$class->id, 'Back to Poll Landing Page') }}
@stop

@section('JS')
    <script type="text/javascript" src="{{asset('js/ractive.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('js/autobahn.min.js')}}"></script>
	<script src='{{ asset('/js/moment.min.js') }}'></script>
@stop

@section('content')
    <div id="stats">
        <script type="text/ractive" id="template">
            <button id="tsv_button" class="btn btn-default" on-click="tsv" style="display: @{{class}}">
                TSV <span class="glyphicon glyphicon-copy"></span>
            </button>
			<table id="stats_table" class="table table-striped fade-in fade-out">			
                <thead>
					<th on-click="sort" id="firstname">First Name</th>
					<th on-click="sort" id="lastname">Last Name</th>
					<th on-click="sort" id="seat">Seat</th>
					<th on-click="sort" id="email">Email</th>
					<th on-click="sort" id="answered_count">Polls Answered</th>
					[[#polls:p]]
						<th on-click="sort" id="[[name]]">[[name]]</th>
					[[/polls]]
                </thead>
                <tbody>
					[[#rows:r]]
					<tr>
						<td>[[firstname]]</td>
						<td>[[lastname]]</td>
						<td>[[seat]]</td>
						<td>[[email]]</td>
						<td>[[answered_count]]</td>
						[[#score_trimmed:s]]
							<td>[[this]]</td>
						[[/score_trimmed]]
					</tr>
					[[/rows]]
                </tbody>
            </table>
        </script>
    </div>

    <script type="text/javascript">
        // hands
            $(document).ready( function() {
                @foreach ($class->users as $student)
                    $('#hands').on('click', '#dismiss_hand_{{ $student->id }}',
                        {
                            studentID: {{ $student->id }},
                            courseID: {{ $class->id }}
                        },
                        dismissHand);
                @endforeach
                getHands();
            });

            function dismissHand(event) {
                var studentID = event.data.studentID;
                var courseID = event.data.courseID;
                $.post('/professor/dismissHand/' + studentID + '/course/' + courseID);
            }

            function getHands(){
                $('#hands').load('{{ route('get_hands_for_course', array('course_id' => $class->id)) }}');
            }
        // end hands

        var data = {{$data}}
        data.results = false;
        console.log(data);
        var ractive = new Ractive ({
            el       : "#stats",
            template : "#template",
            data     : data,
			delimiters: [ '[[', ']]' ]
        });
		
        data.sorts = {
            "First Name": true,
            "Last Name": true,
            Seat: true
        }
        for (var i = 0; i < data.polls.length; i++) {
            data.sorts[data.polls[i].name] = true;
        }
		
        ractive.on("sort", function(event) {
			var sortBy = event.node.id;
            var rows = ractive.get("rows");
            var sorts = ractive.get("sorts");
			
            rows.sort(function(a ,b) {
				if(sortBy == "lastname" || sortBy == "firstname" || sortBy == "seat") {
					var aVal = a[sortBy].toLowerCase();
					var bVal = b[sortBy].toLowerCase();
				}
				else if(sortBy == "answered_count") {
					var aVal = a[sortBy];
					var bVal = b[sortBy];
				}
				else {
					aVal = a.score[event.index.p];
					bVal = b.score[event.index.p];
				}

                if (aVal === bVal) {
                    aVal = a.firstname.toLowerCase();
                    bVal = b.firstname.toLowerCase();
                }

                // not same
                if (aVal === "") {
                    return sorts[sortBy] ? -1 : 1;
                }
                if (bVal === "") {
                    return sorts[sortBy] ? 1 : -1;
                }

                if (aVal < bVal)
                    return sorts[sortBy] ? -1 : 1;
                else if (aVal > bVal)
                    return sorts[sortBy] ? 1 : -1;
                return 0; 
            });
            sorts[sortBy] = !sorts[sortBy];
        }); 

        ractive.on("tsv", function(event) {
            var course = ractive.get("course");
			var assignments = ractive.get("polls");
            var students = ractive.get("rows");

            if (!Array.isArray(students)) {
                var studentsArray = [];
                for (s in students) {
                    studentsArray.push(students[s]);
                }
                students = studentsArray;
            }
            var tsv = "First Name\tLast Name\tSeat\tEmail\tPolls Answered\t";
			
			var assignment_names = [];
			for (var i = 0; i < assignments.length; i++) {
				assignment_names.push(assignments[i].name);
			}
			tsv += assignment_names.join("\t") + "\n";

            for (var i = 0; i < students.length; i++) {
                tsv += students[i].firstname + "\t" + students[i].lastname + "\t" + students[i].seat + "\t" + students[i].email + "\t" + students[i].answered_count + "\t";
                tsv += students[i].score.join("\t");
				tsv += "\n";
            }

            var a = document.createElement("a")
            a.href = "data:attachment/tsv," + encodeURIComponent(tsv);
            a.target = "_blank"
            a.download = course.name + "_poll_results_"+moment().format()+".tsv"

            document.body.appendChild(a);
            a.click();
        }) 

        // Socket magic
        var courseid = ractive.get("course.id")
		//Set the route for the websocket server
		var host = window.location.hostname;
		var protocol = window.location.protocol;
		if(protocol == "http:")
			socketServer="ws://" + host + ":8080";  //Intended for use with localhost
		else
			socketServer="wss://" + host + "/ws/";  //Use ProxyPass redirect on https

        var conn = new ab.Session(socketServer,		//create Autobahn session to talk to websocket
            function() {
                // hands
                conn.subscribe("course_id_" + courseid, function(topic, data) {
                    // console.log('Topic: ' + topic);
                    console.log(data);
                    getHands();
                });
            },
            function() {
                console.warn('WebSocket connection closed');        //on close of websocket
            },
            {'skipSubprotocolCheck': true}
        );

    </script>
@stop
