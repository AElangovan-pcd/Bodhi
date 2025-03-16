<script>
    //Connect to private channel for the individual.
    window.Echo.private('App.User.{{$user->id}}.Course.{{$course->id}}')
        .listen('HandLowered', (e) => {
            placeInLine();
        })
    ;

    //Connect to private channel for the course.
    var course_socket = window.Echo.private('course-all.{{$course->id}}')
        .listen('NewPoll', (e) => {
            console.log(e);
            new_poll();
            poll_noty();
        })
        .listen('ClosePoll', (e) => {
            console.log(e);
            no_poll();
        })
    ;

    $(document).ready(function(){
        placeInLine();
        checkForPolls();
        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        });
    });

    function checkForPolls() {
        $.post('{{url( '/course/'.$course->id.'/polls/check_active' )}}',
            {
                _token: "{{ csrf_token() }}",
            },
            function (data) {
                data = JSON.parse(data);
                console.log(data);

                if (data.active) {
                    new_poll();
                }
                if (data.complete) {
                    complete_poll();
                }
            });
    }

    function new_poll() {
        var button = $.parseHTML(
            '<span class="d-md-none"><i class="fas fa-vote-yea" style="color:green"></i></span>' +
            '<span class="d-none d-md-block"><button type="button" class="btn btn-outline-success btn-sm">New Poll</button></span>')
        $('#polls').html(button);
        $('[data-toggle="tooltip"]').tooltip();
    }

    function poll_noty() {
        @if(!isset($pollPage))  //Don't do the noty if you're on the poll page.
        var n = new Noty({
            theme: 'bootstrap-v4',
            type: 'information',
            layout:'bottomRight',
            text: '<strong>New Poll</strong><br>There is a new poll available. Go to the poll page?  (You can get there from the toolbar as well).',
            buttons: [
                Noty.button('Go!', 'btn btn-success mr-2', function () {
                    console.log("load pressed");
                    window.location.href = '{{url( '/course/'.$course->id.'/polls/participate' )}}';
                    n.close();
                }),
                Noty.button('Ignore', 'btn btn-danger', function() {
                    console.log("no pressed");
                    n.close();
                })
            ]
        }).show();
        @endif
    }

    function no_poll() {
        var button = $.parseHTML(
            '<span class="d-md-none"><i class="fas fa-vote-yea" data-toggle="tooltip" data-placement="bottom" title="No active polls"></i></span>' +
            '<span class="d-none d-md-block"><i class="fas fa-vote-yea" data-toggle="tooltip" data-placement="bottom" title="No active polls"></i></span>')
        $('#polls').html(button);
        $('[data-toggle="tooltip"]').tooltip();
    }

    function complete_poll() {
        var button = $.parseHTML(
            '<span class="d-md-none"><i class="fas fa-vote-yea" style="color:dodgerblue" data-toggle="tooltip" data-placement="bottom" title="Poll complete"></i></span>' +
            '<span class="d-none d-md-block"><i class="fas fa-vote-yea" style="color:dodgerblue" data-toggle="tooltip" data-placement="bottom" title="Poll complete"></i></span>')
        $('#polls').html(button);
        $('[data-toggle="tooltip"]').tooltip();
    }

    function raise_hand() {
        $.post('{{ url('/course/'.$course->id.'/raiseHand') }}',
            {
                _token: "{{ csrf_token() }}",
            },
            function (data)
            {
                $('#output').html(data);
                placeInLine();
            }
        );
    }

    function lower_hand() {
        $.post('{{ url('/course/'.$course->id.'/lowerHand') }}',
            {
                _token: "{{ csrf_token() }}",
            },
            function (data)
            {
                placeInLine();
            }
        );
    }

    function placeInLine() {
        $.post('{{url( '/course/'.$course->id.'/placeInLine' )}}',
            {
                _token: "{{ csrf_token() }}",
            },
            function(data) {
                if (data == 0) {
                    var button = $.parseHTML('<span><button class="btn btn-sm btn-link" id="hand_button" style="cursor: pointer" onclick="raise_hand()">' +
                        '<span class="d-md-none"><i class="fas fa-hand-paper" style="color:gray"></i></span>'+
                        '<span class="d-none d-md-block">Raise your hand!</span>'+
                        '</button></span>');
                    $('#output').hide();
                }
                else {
                    var button = $.parseHTML('<span><button class="btn btn-sm btn-link" id="hand_button" style="cursor: pointer" onclick="lower_hand()">' +
                        '<span class="d-md-none"><i class="fas fa-hand-paper" style="color:green"></i></span>'+
                        '<span class="d-none d-md-block">Lower your hand!</span>'+
                        '</button></span>');
                    $('#output').show();
                }
                $('#hand_button').replaceWith(button);
                $('#output').html(data);
            }
        );
    }
</script>
