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
        @include('instructor.poll.allResultsPage')
    </script>


    <script type="text/javascript">

        var data = {!! $data !!};

        console.log(data);
        var ractive = new Ractive ({
            el       : "#target",
            template : "#template",
            data     : data,
            delimiters: [ '[[', ']]' ],
            computed: {
                results_table: function() {
                    let stud = this.get("course.students");
                    let polls = this.get("course.polls");
                    stud.forEach(s => {
                        s.poll_items = [];
                        s.poll_responses = 0;
                        polls.forEach((p,i) => {
                            s.poll_items[i] = p.poll_answers.find(a => a.user_id === s.id);
                            if(s.poll_items[i] !== undefined) {
                                s.poll_responses++;
                                if(s.poll_items[i].answer.substr(0,10)==="data:image")
                                    s.poll_items[i].answer = '<img src="' + s.poll_items[i].answer + '" width="200">';
                            }
                        });
                    });
                    return stud;
                }
            }
        });



    </script>
@stop
