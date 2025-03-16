@extends('layouts.instructor')

@section('links')
    <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
    <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/polls/landing')}}">Polls</a></li>
@stop

@section('JS')
    <script type="text/javascript" src="{{asset('js/ractive.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('js/canvasjs.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('js/palette.js')}}"></script>
    <script type="text/javascript" src="{{asset('js/Chart.min.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/d3/3.4.11/d3.min.js"></script>
    <script type="text/javascript" src="{{asset('js/cloud.js')}}"></script>
@stop

@section('CSS')
    <style>
        .poll-choice {
            padding: 10px;
            margin: 1px;
            border: 2px solid #AAAAAA;
            border-radius: 20px;
            background-color: #CCCCCC;
            word-wrap: break-word;
        }
    </style>
@stop

@section('content')
    <div id="target"></div>
    <script type="text/ractive" id="template">
        @include('instructor.poll.pollResultsPage')
    </script>


    <script type="text/javascript">
        const restartURL   = "{{url('/instructor/course/'.$course->id.'/polls/restart')}}";
        const completeURL  = "{{url('/instructor/course/'.$course->id.'/polls/complete')}}";
        const duplicateURL  = "{{url('/instructor/course/'.$course->id.'/polls/duplicate')}}";
        const activateURL  = "{{url('/instructor/course/'.$course->id.'/polls/activate')}}";

        var data = {!! $data !!};
        data.results = false;

        data.answerCount= data.initial_data.length;
        console.log(data);
        var ractive = new Ractive ({
            el       : "#target",
            template : "#template",
            data     : data,
            delimiters: [ '[[', ']]' ]
        });

        ractive.set("classroom", false);

        //Initialize the chart for a MC question
        if(data.poll_type == 0) {
            var poll = ractive.get("poll");
            var colors = palette('mpn65', poll.choices.length).map(function (hex) {
                return '#' + hex;
            });
            ractive.set("poll.colors", colors);
            var ctx = $("#myChart");

            var myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: poll.choices,
                    datasets: [{
                        data: poll.votes,
                        backgroundColor: colors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {display: false},
                    scales: {
                        xAxes: [{
                            ticks: {
                                callback: function (label) {
                                    return label.substring(0, 10);  //Get an abbreviated label for the x-axis
                                }
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                precision: 0,
                            }
                        }]
                    },
                    tooltips: {
                        callbacks: {
                            title: function (t, d) {
                                return d.labels[t[0].index];  //Get the full label for the tooltip
                            }
                        }
                    }
                }
            });

        }

        function updateChart() {
            var test = [1, 20, 13, 4, 5];
            console.log(myChart.data.datasets[0]);
            myChart.data.datasets[0].data = test;
            myChart.update();
        }

        ractive.set("mode","overlay");

        var pollType = ractive.get("poll_type");
        if(pollType == 0)
            ractive.set("class","none");
        if(pollType == 1) {
            ractive.set("class","table");

            var poll_data = ractive.get("initial_data")
            var text_string = "";
            for (var i = 0; i < poll_data.length; i++){
                text_string = text_string + " " + poll_data[i].answer;
            }

            ractive.set("text_string", text_string);
            ractive.set("autoRedraw", false);
            ractive.set("ignoreCommon", true);

            drawWordCloud(text_string);

            function drawWordCloud(text_string){
                var ignoreCommon = ractive.get("ignoreCommon");
                if(ignoreCommon)
                    var common = "i,me,my,myself,we,us,our,ours,ourselves,you,your,yours,yourself,yourselves,he,him,his,himself,she,her,hers,herself,it,its,itself,they,them,their,theirs,themselves,what,which,who,whom,whose,this,that,these,those,am,is,are,was,were,be,been,being,have,has,had,having,do,does,did,doing,will,would,should,can,could,ought,i'm,you're,he's,she's,it's,we're,they're,i've,you've,we've,they've,i'd,you'd,he'd,she'd,we'd,they'd,i'll,you'll,he'll,she'll,we'll,they'll,isn't,aren't,wasn't,weren't,hasn't,haven't,hadn't,doesn't,don't,didn't,won't,wouldn't,shan't,shouldn't,can't,cannot,couldn't,mustn't,let's,that's,who's,what's,here's,there's,when's,where's,why's,how's,a,an,the,and,but,if,or,because,as,until,while,of,at,by,for,with,about,against,between,into,through,during,before,after,above,below,to,from,up,upon,down,in,out,on,off,over,under,again,further,then,once,here,there,when,where,why,how,all,any,both,each,few,more,most,other,some,such,no,nor,not,only,own,same,so,than,too,very,say,says,said,shall";
                else
                    var common = "";

                var word_count = {};

                var words = text_string.split(/[ '\-\(\)\*":;\[\]|{},.!?]+/);
                if (words.length == 1){
                    word_count[words[0]] = 1;
                } else {
                    words.forEach(function(word){
                        var word = word.toLowerCase();
                        if (word != "" && common.indexOf(word)==-1 && word.length>1){
                            if (word_count[word]){
                                word_count[word]++;
                            } else {
                                word_count[word] = 1;
                            }
                        }
                    })
                }

                var svg_location = "#chart";
                var width = $('#chart').parent().width();
                var height = 300;//$('#chart').parent().height();

                var fill = d3.scale.category20();

                var word_entries = d3.entries(word_count);

                var xScale = d3.scale.linear()
                    .domain([0, d3.max(word_entries, function(d) {
                        return d.value;
                    })
                    ])
                    .range([10,100]);

                d3.layout.cloud().size([width, height])
                    .timeInterval(20)
                    .words(word_entries)
                    .fontSize(function(d) { return xScale(+d.value); })
                    .text(function(d) { return d.key; })
                    .rotate(function() { return ~~(Math.random() * 2) * 90; })
                    .font("Impact")
                    .on("end", draw)
                    .start();

                function draw(words) {
                    d3.select(svg_location).append("svg")
                        .attr("width", width)
                        .attr("height", height)
                        .append("g")
                        .attr("transform", "translate(" + [width >> 1, height >> 1] + ")")
                        .selectAll("text")
                        .data(words)
                        .enter().append("text")
                        .style("font-size", function(d) { return xScale(d.value) + "px"; })
                        .style("font-family", "Impact")
                        .style("fill", function(d, i) { return fill(i); })
                        .attr("text-anchor", "middle")
                        .attr("transform", function(d) {
                            return "translate(" + [d.x, d.y] + ")rotate(" + d.rotate + ")";
                        })
                        .text(function(d) { return d.key; });
                }

                d3.layout.cloud().stop();
            }
        }

        if(pollType == 2) {
            ractive.set("overlaySize",300);
            ractive.set("individualSize",200);

            ractive.on("overlaymode", function(event) {
                ractive.set("mode","overlay");
            });

            ractive.on("individualmode", function(event) {
                ractive.set("mode","individual");
            });

            ractive.on("select", function(event) {
                console.log(event);
                ractive.set("selected",event.node.id);
            });

            ractive.on("expand",function(event) {
                var size = ractive.get("overlaySize");
                size=size+100;
                ractive.set("overlaySize",size);
            });

            ractive.on("shrink",function(event) {
                var size = ractive.get("overlaySize");
                size=size-100;
                ractive.set("overlaySize",size);
            });

            ractive.on("expand-ind",function(event) {
                var size = ractive.get("individualSize");
                size=size+50;
                ractive.set("individualSize",size);
            });

            ractive.on("shrink-ind",function(event) {
                var size = ractive.get("individualSize");
                if(size>=100)
                    size=size-50;
                ractive.set("individualSize",size);
            });
        }

        ractive.on("redraw",function(event) {
            var text_string = ractive.get("text_string");
            $('#chart').empty();
            drawWordCloud(text_string);
            ractive.set("newResp",false);
        });

        ractive.on("toggleAutoRedraw", function(event) {
            ractive.toggle("autoRedraw");
        });

        ractive.on("toggleIgnoreCommon", function(event) {
            ractive.toggle("ignoreCommon");
            $('#chart').empty();
            drawWordCloud(ractive.get("text_string"));
        });

        ractive.on("toggle_results", function(event) {
            ractive.set("results", !ractive.get("results"));
            document.getElementById("results_table").style.display =
                ractive.get("results") ? "table" : "none";

            document.getElementById("tsv_button").style.display =
                ractive.get("results") ? "block" : "none";
            return;
        });

        ractive.on("toggle_classroom", function(event) {
            if(ractive.get("classroom"))
                $("#classroom").hide();
            else
                $("#classroom").show();
            ractive.toggle("classroom");
        });

        ractive.on("toggle_users", function(event) {
            ractive.toggle('results');
        });

        ractive.on("sort", function(event) {
            var sortBy = event.node.id;
            var students = ractive.get("students");

            //If the table is already sorted by this column, reverse it.
            if(sortBy == ractive.get("sortColumn")) {
                students.reverse();
                this.set("students", students);
                return;
            }

            students.sort(function(a ,b) {
                if(sortBy == "seat") {
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
                    a = a[sortBy].toLowerCase();
                    b = b[sortBy].toLowerCase();
                }

                return a < b ? -1 : 1;
            });
            this.set("sortColumn", sortBy);

        });

        ractive.on("tsv", function(event) {
            var poll = ractive.get("poll");
            var students = ractive.get("students");

            if (!Array.isArray(students)) {
                var studentsArray = [];
                for (s in students) {
                    studentsArray.push(students[s]);
                }
                students = studentsArray;
            }
            var tsv = poll.name + "\t" + poll.question + "\n";
            tsv += "Student Name \t Email \t Answer \n"

            for (var i = 0; i < students.length; i++) {
                tsv += students[i].firstname + " " + students[i].lastname + "\t" + students[i].email + "\t";
                tsv += (students[i].answer !== null ? students[i].answer : "") + "\n";
            }

            var a = document.createElement("a")
            a.href = "data:attachment/tsv," + encodeURIComponent(tsv);
            a.target = "_blank"
            a.download = poll.name + "_answers.tsv"

            document.body.appendChild(a);
            a.click();
        });

        ractive.on('complete_poll', function (event) {

            $.post(completeURL, {_token: "{{ csrf_token() }}",id: data.poll.id}, function (resp) {
                console.log(resp);
                ractive.set("poll.complete",1);
                ractive.update();
            });
        });

        ractive.on('allow_answers', function (event) {
            console.log(event);
            ractive.set("openMsg", null);

            $.post(restartURL, {_token: "{{ csrf_token() }}",id: data.poll.id}, function (resp) {
                console.log(resp);
                if(resp=="fail") {
                    ractive.set("openMsg", "Another poll is open.  Close it first.");
                    return;
                }
                ractive.set("poll.complete",0);
                ractive.update();
            });

        });

        ractive.on('duplicate', function (event) {
            console.log(event);
            ractive.set("openMsg","Duplicating...");
            $.post(duplicateURL, {_token: "{{ csrf_token() }}",id: data.poll.id}, function (resp) {
                response = JSON.parse(resp);
                console.log(response);
                var dup_id = response.id;
                ractive.set("openMsg","Duplicated. Activating...");
                $.post(activateURL, {_token: "{{ csrf_token() }}",id: dup_id}, function (resp) {
                    console.log(resp);
                    if(resp=="fail") {
                        ractive.set("openMsg", "Duplicated, but could not launch because another poll is open.");
                        return;
                    }
                    //Go to new results page
                    window.location.href = dup_id;
                });
            });
        });

        window.Echo.private('course-instructor.{{$course->id}}')
            .listen('PollAnswered', (e) => {
                console.log(e);
                ractive.add('answerCount');
                var answer = e.answer;
                var choices = ractive.get("poll.choices");
                var votes = ractive.get("poll.votes");
                var students = ractive.get("students");

                for (i = 0; i < students.length; i++) {
                    if (students[i].id === answer.user_id)
                        students[i].answer = answer.answer;
                }

                ractive.set("students", students);
                if(ractive.get("poll_type") == 0) {
                    for (var i = 0; i < choices.length; i++){        //check which answer was submitted, update chart
                        if(choices[i] == answer.answer) {
                            votes[i]++;
                        }
                    }
                    myChart.update();
                    addSeats(canvas);
                }
                else if(ractive.get("poll_type") == 1) {
                    console.log("type 1 received");
                    var text_string = ractive.get("text_string");
                    text_string = text_string + " " + answer.answer;
                    ractive.set("text_string",text_string);

                    if(ractive.get("autoRedraw")) {
                        $('#chart').empty();
                        drawWordCloud(text_string);
                    }
                    else
                        ractive.set("newResp",true);
                }
            })
        ;

        //Classroom layout
        function initSeats() {
            let seats = ractive.get("course.seats");
            let students = ractive.get("students");
            students.forEach((s, si) => {
                let stuSeat = s.pivot.seat ?? "";
                let seat = seats.find(se => se.name.toLowerCase() === stuSeat.toLowerCase());
                if (seat == undefined)
                    s.seat = null;
                else
                    s.seat = seat;
            });
        }

        initSeats();

        if(ractive.get("poll_type") == 0) {
            //Build associative array of choice colors:
            var choiceColors = [];
            var choices = ractive.get("poll.choices");
            var colors = ractive.get("poll.colors");
            for(i=0; i<choices.length; i++) {
                choiceColors[choices[i]] = colors[i];
            }

            //Get the list of students who don't have a matching seat position
            var noSeats = [];
            var students = ractive.get("students");
            for(var key in students) {
                if(students[key].seat == null)
                    noSeats.push(students[key]);
            }
            ractive.set("noSeats", noSeats);

            //Create the classroom canvas
            var canvas = document.getElementById('classCanvas');
            var img = new Image();
            img.src = "{{ url('instructor/course/'.$course->id.'/classroomImage?'.time()) }}";  //Time is added to the filename to prevent browser from cacheing old image after upload.

            img.onload = function () {

                var dpr = 2;  //Make the canvas too big at first to improve image resolution.
                var rect = canvas.getBoundingClientRect();

                canvas.width = rect.width * dpr;
                canvas.height = canvas.width;//rect.height * dpr;
                var ctx = canvas.getContext('2d');

                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

                //Scale everything back down to fit.
                canvas.style.width = canvas.parentNode.offsetWidth - 40 + 'px';
                if (canvas.parentNode.offsetWidth > window.innerHeight * .75)   //Make sure it all fits on a vertical page
                    canvas.style.width = window.innerHeight * .75 + 'px';
                canvas.style.height = canvas.style.width;

                $("#classroom").hide();
                addSeats(canvas);
            };



            function addSeats(canvas) {
                var ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                var students = ractive.get("students");
                ctx.font = "bold 16px Arial";
                ctx.textAlign = 'center';
                var color;
                var focus = ractive.get("focus");

                for (var key in students) {
                    if(students[key].seat == null)
                        continue;
                    color = 'black';
                    if((focus == null && students[key].answer != "") || choices[focus] == students[key].answer ) {
                        color = choiceColors[students[key].answer];
                    }
                    ctx.fillStyle = color;
                    ctx.fillText(students[key].firstname, students[key].seat.x * canvas.width, students[key].seat.y * canvas.height);
                    //TODO handle students who don't have a matching seat
                }
            }

            window.addEventListener('resize', function (evt) {
                canvas.style.width = canvas.parentNode.offsetWidth - 40 + 'px';
                canvas.style.height = canvas.parentNode.offsetWidth - 40 + 'px';
            })
        }

        ractive.on("focus", function(event) {
            if(event.node.id==-1)
                ractive.set("focus",null);
            else
                ractive.set("focus",event.node.id);
            addSeats(canvas);
        });

    </script>
@stop
