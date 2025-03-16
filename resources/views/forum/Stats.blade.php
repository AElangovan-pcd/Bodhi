@extends('layouts.student')

@section('links')
    <li class="nav-item"><a class="nav-link" href="{{url('course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
    <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/forum/landing')}}">Forum</a></li>
@stop

@section('JS')
    <script src="{{ asset('js/ractive.min.js') }}"></script>
@stop

@section('content')

    <div id="target"></div>

    <script id="template" type="text/ractive">
        @include('forum.statsPage')
    </script>

    <script type="text/javascript">

        var data = {!! $data !!};
        data.results = false;
        console.log(data);
        var ractive = new Ractive ({
            el       : "#target",
            template : "#template",
            data     : data,
			delimiters: [ '[[', ']]' ]
        });
		
        ractive.on("sort", function(event) {
            var sortBy = event.node.id;
            var students = ractive.get("users");
            var sorts = ractive.get("sorts");
            console.log(sortBy);

            //If the table is already sorted by this column, reverse it.
            if(sortBy == ractive.get("sortColumn")) {
                students.reverse();
                ractive.set("students", students);
                ractive.update();
                return;
            }
			
			students.sort(function(a ,b) {
				if(sortBy == "lastname" || sortBy == "firstname" || sortBy == "seat") {
				    if(a[sortBy]==null)
				        var aVal = "";
				    else
					    var aVal = a[sortBy].toLowerCase();
				    if(b[sortBy]==null)
				        var bVal = "";
				    else
					    var bVal = b[sortBy].toLowerCase();
				}
				else {
					aVal = a[sortBy];
					bVal = b[sortBy];
				}

                if (aVal === bVal) {
                    aVal = a.firstname.toLowerCase();
                    bVal = b.firstname.toLowerCase();
                }

                return aVal < bVal ? -1 : 1;
            });
            ractive.set("students",students);
            ractive.set("sortColumn", sortBy);
            ractive.update();
        });

        ractive.on("tsv", function(event) {
            var course = ractive.get("course");
            var students = ractive.get("users");

            if (!Array.isArray(students)) {
                var studentsArray = [];
                for (s in students) {
                    studentsArray.push(students[s]);
                }
                students = studentsArray;
            }
            
			var tsv = "First Name\tLast Name\tSeat\t";
			tsv += "Topics Viewed\tTopics Posted\tResponses Posted \tHelpful Votes Made\tResponses Voted Helpful\tHelpful Votes Received\tInstructor Endorsements\n";

            for (var i = 0; i < students.length; i++) {
                tsv += students[i].firstname + "\t" + students[i].lastname + "\t";
				tsv += students[i].seat + "\t";
                tsv += students[i].views + "\t" + students[i].posts + "\t" + students[i].responses + "\t" + students[i].yourVotes + "\t" + students[i].helpfulAnswers + "\t" + students[i].helpfulVotes + "\t" + students[i].endorsed;
				tsv+= "\n";
            }

            var a = document.createElement("a")
            a.href = "data:attachment/tsv," + encodeURIComponent(tsv);
            a.target = "_blank"
            a.download = course.name + "_forum_stats.tsv"

            document.body.appendChild(a);
            a.click();
        });

    </script>
@stop
