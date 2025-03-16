@extends('layouts.instructor')

@section('JS')
    <script src="{{ asset('js/ractive.min.js') }}"></script>
    <script src="{{ asset('js/papaparse.min.js') }}"></script>
@endsection

@section('links')
    <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
@endsection

@section('content')

    <div id="target"></div>

    <script id="template" type="text/ractive">
        @include('instructor.course.courseTotalsPage')
    </script>


    <script>
        var data = {!! $data !!};
        console.log(data);


        data = setup(data);
        console.log(data);

        function setup(data) {
            data.course.students.forEach(s => {
                s.totals = [];
                data.course.assignments.forEach(a => {
                    //Filter results to contain only those for the current student
                    let results = a.results.filter(r => r.user_id === s.id);
                    //In case duplicate result records got created for a student for a question, keep only one
                    let unique = results.filter((v,i,a)=>a.findIndex(t=>(t.question_id === v.question_id))===i);
                    //Sum all of the scores for this student and push it on as their assignment score
                    let score = unique.reduce((old,cur) => old + cur.earned,0);
                    s.totals.push(score);
                });
            });

            //Get the assignment total.
            data.course.assignments.forEach(a => {
                //a.total = a.questions.filter(q=>q.type !== 4).reduce((old,cur) => old + cur.max_points, 0);
                a.total = a.questions.reduce((old,cur) => old + cur.max_points, 0);
            });

            return data;
        }

        var ractive = new Ractive({
            target: '#target',
            template: '#template',
            data: data,
            delimiters: [ '[[', ']]' ],
        });

        ractive.on("sort", function (context, column) {
            let array = ractive.get("course.students");

            console.log(column);
            //If the table is already sorted by this column, reverse it.
            if(column === ractive.get("sortColumn")) {
                array.reverse();
                this.set("course.students", array);
                return;
            }

            array.sort(function(a,b) {
                if(column === 'first') {
                    a = a.firstname.toLowerCase();
                    b = b.firstname.toLowerCase();
                }
                else if(column === 'last') {
                    a = a.lastname.toLowerCase();
                    b = b.lastname.toLowerCase();
                }
                else if(column === 'seat') {
                    if(a.pivot.seat == null)
                        a = "";
                    else
                        a = a.pivot.seat.toLowerCase();
                    if(b.pivot.seat == null)
                        b = "";
                    else
                        b = b.pivot.seat.toLowerCase();
                }
                else {
                    a = a.totals[column];
                    b = b.totals[column];
                }

                return a < b ? -1 : 1;
            });

            this.set("course.students",array);
            this.set("sortColumn", column);
        });

        ractive.on("csv", function(context) {
            var assignments = ractive.get("course.assignments");
            var students = ractive.get("course.students");

            var csv = [];
            let headers = ["First Name","Last Name","Seat","Email"];

            for (let i=0; i < assignments.length; i++) {
                let line = assignments[i].name.trim();
                line += " (" + assignments[i].total+")";
                headers.push(line);
            }
            csv.push(headers);

            for (let i = 0; i < students.length; i++) {
                var row = [students[i].firstname, students[i].lastname, students[i].pivot.seat,students[i].email];
                for (let j = 0; j < assignments.length; j++) {
                    row.push(students[i].totals[j]);
                }
                csv.push(row);
            }
            for (var i = 0; i < csv.length; i++) {
                csv[i] = csv[i].join("\t");
            }
            csv = csv.join("\n");
            ractive.set("csv_text", csv);
        });

        ractive.on("csv_download", function (event) {
            let csv_text = ractive.get("csv_text");
            let a = document.createElement("a");
            a.href        = 'data:attachment/csv,' +  encodeURIComponent(csv_text);
            a.target      = '_blank';
            a.download    = ractive.get("course").name + "_totals.tsv";

            document.body.appendChild(a);
            a.click();
        });
    </script>

@endsection
