@extends('layouts.instructor')

@section('links')
    <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
@stop

@section('JS')
    <script src="{{ asset('js/ractive.min.js') }}"></script>
    <script src='{{ asset('/js/moment.min.js') }}'></script>
@endsection

@section('content')

    <div id="target"></div>

    <script id="template" type="text/ractive">
        @include('instructor.info.resultsPage')
    </script>

    <script type="text/javascript">
        var data = {!! $data !!};

        console.log(data);

        function total(answers) {
            sum = 0;
            for(j=0; j<answers.length; j++) {
                if(answers[j].hasOwnProperty('earned'))
                    sum += answers[j].earned;
            }
            return sum;
        };

        function format_deadline(answers,closed) {
            if(answers[0].hasOwnProperty('created_at')) {
                return moment(closed).diff(moment(answers[0].created_at),'hours');
            }
            return "-";
        }

        for(i=0; i<data.rows.length; i++) {
            data.rows[i].total = total(data.rows[i].answers);
            data.rows[i].deadline = format_deadline(data.rows[i].answers,data.quiz.closed);
        }

        var ractive = new Ractive({
            el: "#target",
            template: "#template",
            data: data,
            delimiters: [ '[[', ']]' ],

        });

        ractive.on("sort", function(event) {
            var col = event.node.id;
            var rows = ractive.get("rows");

            if(col == ractive.get("sortedBy")) {
                rows.reverse();
                ractive.update();
                return;
            }

            rows.sort(function(a,b) {
                if(!isNaN(col)) {
                    a = a.answers[col].earned;
                    b = b.answers[col].earned;
                }
                else {
                    a = a[col];
                    b = b[col];
                }
                if(a == undefined || a== '-')
                    a = -1;
                if(b == undefined || a== '-')
                    b = -1;
                return a < b ? -1 : 1;
            });

            ractive.set("sortedBy", col);
            ractive.update();
        });

        ractive.on("csv", function(event) {
            var csv = [];
            var headers = [];
            var rows = ractive.get("rows");
            var questions = ractive.get("questions");

            headers.push("First Name", "Last Name", "Seat", "Email", "Total");
            for (i = 0; i<questions.length; i++)
                headers.push("q"+(i+1));
            csv.push(headers);

            for(i = 0; i<rows.length; i++) {
                var row = [rows[i].firstname, rows[i].lastname, rows[i].seat, rows[i].email, rows[i].total];
                for(j = 0; j< rows[i].answers.length; j++) {
                    row.push(rows[i].answers[j].earned == undefined ? "" : rows[i].answers[j].earned);
                }
                csv.push(row);
            }
            console.log(csv);
            for (var i = 0; i < csv.length; i++) {
                csv[i] = csv[i].join(",");
            }
            csv = csv.join("\n");
            let a = document.createElement("a");
            a.href        = 'data:attachment/csv,' +  encodeURIComponent(csv);
            a.target      = '_blank';
            a.download    = "information_quiz_results.csv";

            document.body.appendChild(a);
            a.click();
        });

    </script>
@stop
