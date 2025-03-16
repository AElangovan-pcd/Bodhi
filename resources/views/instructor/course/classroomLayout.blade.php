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
        @include('instructor.course.classroomLayoutPage')
    </script>


    <script>
        var data = {!! $data !!};
        console.log(data);

        var SAVE_PATH = "{{url('instructor/course/'.$course->id.'/saveLayout')}}";
        var TEMPLATE_PATH = "{{url('instructor/course/'.$course->id.'/loadTemplate')}}";
        var REMOVE_TEMPLATE_PATH = "{{url('instructor/course/'.$course->id.'/removeTemplate')}}";

        var ractive = new Ractive({
            target: '#target',
            template: '#template',
            data: data,
            delimiters: [ '[[', ']]' ],
        });

        ractive.set("save_msg","Save");

        var img = new Image();
        var canvas = document.getElementById('classCanvas');

        img.src = "{{ url('instructor/course/'.$course->id.'/classroomImage?'.time()) }}";  //Time is added to the filename to prevent browser from cacheing old image after upload.

        img.onload = function () {

            var dpr = 2;  //Make the canvas too big at first to improve image resolution.
            var rect = canvas.getBoundingClientRect();

            canvas.width = rect.width * dpr;
            canvas.height = canvas.width;//rect.height * dpr;
            var ctx = canvas.getContext('2d');

            ctx.drawImage(img, 0, 0,canvas.width,canvas.height);

            //Scale everything back down to fit.
            canvas.style.width=canvas.parentNode.offsetWidth-40+'px';
            canvas.style.height=canvas.parentNode.offsetWidth-40+'px';

            addSeats(canvas);
        }


        function getMousePos(canvas, evt) {
            var rect = canvas.getBoundingClientRect();
            return {
                x: (evt.clientX - rect.left)/rect.width,
                y: (evt.clientY - rect.top)/rect.height
            };
        }

        function addSeats(canvas) {

            var ctx = canvas.getContext('2d');
            ctx.clearRect(0,0,canvas.width,canvas.height);
            ctx.drawImage(img, 0, 0,canvas.width,canvas.height);
            var seats = ractive.get("seats");
            ctx.font = "16px Arial";
            ctx.textAlign = 'center';

            for(i=0;i<seats.length;i++) {
                ctx.fillText(seats[i].name,seats[i].x*canvas.width,seats[i].y*canvas.height);
            }
        }

        canvas.addEventListener('click', function(evt) {
            var mousePos = getMousePos(canvas, evt);
            var seats = ractive.get("seats");
            var selected = ractive.get("selectedSeat");
            if(selected == undefined)
                return;
            seats[selected].x = mousePos.x;
            seats[selected].y = mousePos.y;
            ractive.set("seats", seats);

            addSeats(canvas);
        }, false);

        window.addEventListener('resize', function(evt) {
            canvas.style.width=canvas.parentNode.offsetWidth-40+'px';
            canvas.style.height=canvas.parentNode.offsetWidth-40+'px';
        });

        ractive.on("select-seat", function(context) {

            seats = ractive.get("seats");
            index = parseInt(context.node.id);

            ractive.set("selectedSeat",index);
        });

        ractive.on("delete", function(context) {

            setTimeout(function(){ //Use timeout to make sure mouse event completes and the seat does not get selected again.
                seats = ractive.get("seats");
                index = parseInt(context.node.id);;
                ractive.set("selectedSeat", -1);
                seats.splice(index,1);
                ractive.set("seats",seats);
                addSeats(canvas);
            }, 100);

        });

        ractive.on("add", function(context) {
            seats = ractive.get("seats");
            var newSeat={name:"",id:-1};
            seats.push(newSeat);
            this.set("seats",seats);
        });

        ractive.on("save", function(context) {
            ractive.set("save_msg","Saving...");
            seats = this.get("seats");

            $.post(SAVE_PATH,
                {
                    _token: "{{ csrf_token() }}",
                    seats: seats,
                })
                .done(function (response) {
                    response = JSON.parse(response);
                    console.log(response);
                    ractive.set("seats",response.updated);
                    addSeats(canvas);
                    ractive.set("save_msg","Saved");
                })
                .fail(function(xhr,status,error) {  //Deal with post failure.  Suggest page reload in case it's a session timeout.
                    ractive.set("save_msg","Error Saving");
                    console.log(error);
                });
        });

        ractive.on("export", function(context) {
           var seats = this.get("seats");
           var csv="";
           for(i=0; i<seats.length; i++) {
               csv += seats[i].name + "," + seats[i].x + "," + seats[i].y + "\n";
           }
           console.log(csv);

            var a = document.createElement("a");
            a.href        = 'data:attachment/csv,' +  encodeURIComponent(csv);
            a.target      = '_blank';
            a.download    = "classroom_layout.csv";

            document.body.appendChild(a);
            a.click();
        });

        function handleFileSelect(evt) {
            var file = evt.target.files[0];
            Papa.parse(file, {
                header: false,
                dynamicTyping: true,
                skipEmptyLines: true,
                complete: function(results) {
                    importSeats(results.data);
                }
            });
            $("#importModal").modal('hide')
        }

        $(document).ready(function(){
            $("#csv-file").change(handleFileSelect);
        });

        function importSeats(seatList) {
            console.log(seatList);
            var seats = [];
            for(i=0; i<seatList.length; i++) {
                var newSeat={name:seatList[i][0],id:-1,x:seatList[i][1],y:seatList[i][2]};
                seats.push(newSeat);
            }
            ractive.set("seats",seats);
            addSeats(canvas);
        }

        //Include the name in the image upload dialog
        $('#layoutImage').on('change',function(){
            //get the file name
            var fileName = $(this).val();
            fileName = fileName.replace(/^.*\\/, "");
            //replace the "Choose a file" label
            $(this).next('.custom-file-label').html(fileName);
            console.log(fileName);
        });

        $('#csv-file').on('change',function(){
            //get the file name
            var fileName = $(this).val();
            fileName = fileName.replace(/^.*\\/, "");
            //replace the "Choose a file" label
            $(this).next('.csv-file-label').html(fileName);
            console.log(fileName);
        });

        ractive.on("template", function(context) {
            console.log(context);
            var filename = context.node.id;
            $.post(TEMPLATE_PATH,
                {
                    _token: "{{ csrf_token() }}",
                    filename: filename,
                })
                .done(function (response) {
                    location.reload();
                })
                .fail(function(xhr,status,error) {  //Deal with post failure.
                    console.log(error);
                });
        });

        ractive.on("remove-template", function(context) {
            console.log(context);

            $.post(REMOVE_TEMPLATE_PATH,
                {
                    _token: "{{ csrf_token() }}",
                    id: context.node.id,
                })
                .done(function (response) {
                    console.log(response);
                    ractive.set("classrooms",JSON.parse(response));
                })
                .fail(function(xhr,status,error) {  //Deal with post failure.
                    console.log(error);
                });
            return false;  //Prevent event from bubbling up to selecting the template.
        });
    </script>

    </script>


@endsection
