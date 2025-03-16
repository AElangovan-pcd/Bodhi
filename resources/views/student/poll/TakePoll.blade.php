@extends('layouts.student')

@section('hand_links')
    <a id="hand_raise_button" >Raise your hand!</a>
    <li><a id="hand_lower_button" >Lower your hand!</a></li>
    <li><a class="btn btn-primary disabled" style="color:#fff" id="output"></a></li>
@stop
@section('links')
    <li class="nav-item"><a class="nav-link" href="{{url('course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
@stop

@section('JS')
    <script type="text/javascript" src="{{asset('js/ractive.min.js')}}"></script>
@stop

@section('CSS')
    <style>
        .poll-choice {
            padding: 10px;
            margin: 10px;
            border: 2px solid #AAAAAA;
            border-radius: 20px;
            background-color: #CCCCCC;
            word-wrap: break-word;
        }
        .chosen {
            background-color: #FEFEFE;
        }
    </style>
@stop

@section('content')
    <div id="target"></div>

    <script type="text/ractive" id="template">
        @include('student.poll.takePollPage')
    </script>


    <script type="text/javascript">
        function choose(id) {
            $(".poll-choice").removeClass('chosen');
            $("#c_" + id).addClass('chosen');
        }
        var course = {!! $course !!};
        var data = {!! $data !!};
        console.log(data);

        var ractive = new Ractive({
            el       : "#target",
            template : "#template",
            data     : data,
            delimiters: [ '[[', ']]' ]
        });

        //Connect to private channel for the course.
        course_socket
            .listen('NewPoll', (e) => {
                console.log(e);
                getPoll();
            })
            .listen('ClosePoll', (e) => {
                console.log(e);
                clearPoll();
            })
        ;

        function clearPoll() {
            ractive.set("poll",null);
            ractive.set("poll.type",null);
            ractive.set("answer",null);
            ractive.set("answered",null);
            ractive.set("choice_index",null);
            ractive.set("SAval", null);
            ractive.update();
        }

        function getPoll() {
            clearPoll();
            $.post('{{url( '/course/'.$course->id.'/polls/get' )}}',
                {
                    _token: "{{ csrf_token() }}",
                },
                function (resp) {
                    console.log(resp);
                    data.poll = resp.poll;
                    //data.poll.type = resp.poll.type;
                    //data.answer = resp.answer;
                    //data.answered = resp.answered;
                    //data.choice_index = resp.choice_index;
                    ractive.update();
                    init_poll();
                }
            );
        }

        ractive.on('choose', function(event) {
            console.log(event);
            ractive.set('choice_index', event.node.id);
            choose(event.node.id);
            console.log(ractive.get());
        });

        ractive.on('submit', function (event) {
            let poll = ractive.get("poll");
            if(poll.type == 2)
                var dataURL = myCanvas.toDataURL();

            var data = {
                poll: ractive.get("poll"),
                choice_index: ractive.get("choice_index"),
                short_answer: ractive.get("SAval"),
                drawing: dataURL
            }
            console.log(data);
            $.post("{{url( '/course/'.$course->id.'/polls/submit' )}}", {
                    _token: "{{ csrf_token() }}",
                    data: data
                },
                function (resp) {
                    console.log(resp);
                    ractive.set('answered', true);
                    $("#submit").hide();
                    checkForPolls();
                })
        })

        function init_poll() {
            let poll = ractive.get("poll");
            if(poll == null)
                return;

            answer = poll.answer;

            if (poll.choices != null)
                poll.choices = poll.choices.split(" | ");

            if (poll.complete === 0) {
                if (poll.answer !== null) {
                    ractive.set('answered', true);
                    if (poll.choices !== null)
                        ractive.set('choice_index', poll.choices.findIndex(x => x === poll.answer.answer));
                    ractive.set('SAval',poll.answer.answer);
                }
            }
            else
                ractive.set('answered', true);


            ractive.set("answer", answer);

            if(poll.type == 2) {
                var myCanvas = document.getElementById("myCanvas");
                var ctx = myCanvas.getContext("2d");

                var pwidth = $(window).width();
                var canvasDim = 400;
                if(pwidth < 420)
                    canvasDim = pwidth-40;

                var img=document.getElementById("bgImage");

                bgImage.src=ractive.get("poll.image");

                bgImage.width = canvasDim;
                bgImage.height = canvasDim;

                // Fill Window Width and Height
                myCanvas.width = canvasDim;
                myCanvas.height = canvasDim;

                myCanvas.setAttribute('style', 'border:1px solid;');

                //Put canvas on top of background image:
                myCanvas.style.position = "absolute";
                myCanvas.style.left = img.offsetLeft + "px";
                myCanvas.style.top = img.offsetTo + "px";
                myCanvas.style.margin.top = "0 px";

                // Set Background to Transparent
                ctx.fillStyle="rgba(0,0,200,0)";
                ctx.fillRect(0,0,myCanvas.width,myCanvas.height);

                //If the poll has already been answered, load the image they used.
                var answered = ractive.get("answered");
                if(answered == true) {
                    var img = new Image();
                    img.src = ractive.get("SAval");
                    img.onload = function() {
                        ctx.drawImage(img,0,0);
                    };
                }

                var color = "#3498DB";
                if(answered != 1) { //Only allow drawing if the question has not been answered.
                    // Mouse Event Handlers
                    if(myCanvas){
                        var isDown = false;
                        var canvasX, canvasY;
                        ctx.lineWidth = 5;

                        $(myCanvas)
                            .mousedown(function(e){
                                isDown = true;
                                ctx.beginPath();
                                canvasX = e.offsetX;//e.pageX - myCanvas.offsetLeft;
                                canvasY = e.offsetY;//e.pageY - myCanvas.offsetTop;
                                //console.log(e);
                                ctx.moveTo(canvasX, canvasY);
                            })
                            .mousemove(function(e){
                                if(isDown !== false) {
                                    canvasX = e.offsetX;//e.pageX - myCanvas.offsetLeft;
                                    canvasY = e.offsetY;//e.pageY - myCanvas.offsetTop;
                                    ctx.lineTo(canvasX, canvasY);
                                    ctx.strokeStyle = color;
                                    ctx.stroke();
                                }
                            })
                            .mouseup(function(e){
                                isDown = false;
                                ctx.closePath();
                            });
                    }

                    // Touch Events Handlers
                    draw = {
                        started: false,
                        start: function(evt) {
                            var rect = myCanvas.getBoundingClientRect();
                            ctx.beginPath();
                            ctx.moveTo(
                                evt.touches[0].clientX - rect.left,
                                evt.touches[0].clientY - rect.top
                            );

                            this.started = true;

                        },
                        move: function(evt) {

                            if (this.started) {
                                var rect = myCanvas.getBoundingClientRect();
                                ctx.lineTo(
                                    evt.touches[0].clientX - rect.left,
                                    evt.touches[0].clientY - rect.top
                                );

                                ctx.strokeStyle = color;
                                ctx.lineWidth = 3;
                                ctx.stroke();
                            }

                        },
                        end: function(evt) {
                            this.started = false;
                        }
                    };

                    // Touch Events
                    myCanvas.addEventListener('touchstart', draw.start, false);
                    myCanvas.addEventListener('touchend', draw.end, false);
                    myCanvas.addEventListener('touchmove', draw.move, false);

                    //Prevent touches on canvas from moving page.
                    myCanvas.addEventListener('touchmove',function(evt){
                        evt.preventDefault();
                    },false);

                    document.getElementById('clear').addEventListener('click', function() {
                        ctx.clearRect(0, 0, myCanvas.width, myCanvas.height);
                    }, false);

                    document.getElementById('black').addEventListener('click', function() {
                        color="#000";
                    }, false);

                    document.getElementById('red').addEventListener('click', function() {
                        color="#E74C3C";
                    }, false);

                    document.getElementById('blue').addEventListener('click', function() {
                        color="#3498DB";
                    }, false);

                    document.getElementById('green').addEventListener('click', function() {
                        color="#2ECC71";
                    }, false);

                    document.getElementById('purple').addEventListener('click', function() {
                        color="#8E44AD";
                    }, false);

                    document.getElementById('orange').addEventListener('click', function() {
                        color="#F3B212";
                    }, false);

                }
                //};
            }
            ractive.update();
        }

        init_poll();
    </script>
@stop
