@extends($instructor ? 'layouts.instructor' : 'layouts.student')

@if($instructor == 1)
@section('links')
    <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
    <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/assignment/'.$assignment_id.'/results/main')}}">Results</a></li>
    <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/assignment/'.$assignment_id.'/edit')}}">Edit</a></li>
@stop

@else
@section('links')
    <li class="nav-item"><a class="nav-link" href="{{url('course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
@stop
@endif

@section('JS')
    <script src="{{ asset('js/ractive.min.js') }}"></script>
@stop

@section('content')

    <div id="target"></div>

    <script id="template" type="text/ractive">
        @include('student.sharePage')
    </script>

    <script type="text/javascript">

        var data = {!! $data !!};

        data.csv = "";
        data.sorts = [];

        /*for (var i = 0; i < data.variables.length; i++) {
            data.sorts[data.variables[i].name] = true;
        }*/

        console.log(data);

        var ractive = new Ractive ({
            template: '#template',
            el: "#target",
            data: data,
            delimiters: [ '[[', ']]' ],
        });

        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        });

        //TODO sockets


        ractive.on("sort", function(event) {
            let array = ractive.get("rows");
            let index = event.node.id;
            if(index === ractive.get("sortColumn")) {
                array.reverse();
                ractive.update();
                return;
            }

            let variable = event.get();

            //let var_type = ractive.get()

            array.sort(function(a,b) {
                a = a[index];
                b = b[index];
                if(variable.type==0) {
                    a = Number(a);
                    b = Number(b);
                }
                if(variable.type==2) {
                    a = a.toLowerCase();
                    b = b.toLowerCase();
                }
                return a < b ? -1 : 1;
            });
            this.set("sortColumn", index);
            ractive.update();

        });



        ractive.on("csv", function(event) {
            var variables = ractive.get('variables');
            var rows = ractive.get('rows');

            /* this check needed because JSON gives rows as an object
             * ie rows = {1: [a,b,c], 3: [x,y,z]}
             * and we just need it to be an array
             * so rows = [[a,b,c], [x,y,yz]]
             */
            if (!Array.isArray(rows)) {
                var rowsArray = [];
                for (r in rows) {
                    if (!rows.hasOwnProperty(r)) continue;
                    rowsArray.push(rows[r]);
                }
                console.log(rowsArray);
                rows = rowsArray
            }

            //var csv = [['First\tLast\tSeat']];
            var csv = [];
            var titlerow = [];
            for (var i = 0; i < variables.length; i++) {
                titlerow.push(variables[i].title + ' | ' + variables[i].descript);
                //csv[0].push(variables[i].title + ' | ' + variables[i].descript);
            }
            csv.push(titlerow.join('\t'));
            for (var i = 0; i < rows.length; i++) {
                var row = [];
                for (var j = 0; j < rows[i].length; j++)
                    row.push(rows[i][j])
                csv.push(row.join('\t'));
            }
            csv = csv.join("\n");
            ractive.set('csv', csv);
        });

        ractive.on('csv_download', function (event) {
            var csv = ractive.get("csv");
            var a = document.createElement("a");
            a.href        = 'data:attachment/csv,' +  encodeURIComponent(csv);
            a.target      = '_blank';
            //a.download    = ractive.get("version").name + "_shared.csv";
            a.download    = ractive.get("assignment_name") + "_shared.tsv";

            document.body.appendChild(a);
            a.click();
        });

    </script>

@endsection
