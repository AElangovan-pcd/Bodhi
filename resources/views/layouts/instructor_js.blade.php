<script>
    //Connect to private channel for course events.  Only course owner can subscribe to this channel.
    window.Echo.private('course-instructor.{{$course->id}}')
        .listen('HandRaised', (e) => {
            getHands();
        })
        .listen('HandLowered', (e) => {
            getHands();
        })
    ;

    var course_socket = window.Echo.private('course-all.{{$course->id}}')
        .listen('NewPoll', (e) => {
            console.log(e);
            active_poll(e.poll_id);
        })
        .listen('ClosePoll', (e) => {
            console.log(e);
            no_poll();
        })
    ;

    $(document).ready( function() {
        getHands();
        checkForPolls();
        $('[data-toggle="tooltip"]').tooltip()
    });

    function getHands(){
        $('#hands').load('{{ route('get_hands_for_course', array('course_id' => $course->id)) }}');
    }

    function dismissHand(e) {
        $.post('{{url('course/'.$course->id.'/dismiss_hand')}}'+'/'+e.id, {_token: "{{ csrf_token() }}"});
        getHands();
    }

    function checkForPolls() {
        $.post('{{url( '/course/'.$course->id.'/polls/check_active' )}}',
            {
                _token: "{{ csrf_token() }}",
            },
            function (data) {
                data = JSON.parse(data);
                console.log(data);

                if (data.active || data.complete)
                    active_poll(data.id);
            });
    }

    function active_poll(id) {
        var button = $.parseHTML(
            '<a class="btn btn-sm btn-outline-success" data-toggle="tooltip" data-placement="bottom" title="Active Poll" href="{{url( '/instructor/course/'.$course->id.'/polls/results' )}}'+'/'+id+'">\n' +
            '    <i class="fas fa-vote-yea" style="color:green"></i>\n' +
            '</a>')
        $('#polls').html(button);
        $('[data-toggle="tooltip"]').tooltip();
    }

    function no_poll() {
        var button = $.parseHTML(
            '<a class="btn btn-sm btn-outline-dark" data-toggle="tooltip" data-placement="bottom" title="No active polls" href="{{url( '/instructor/course/'.$course->id.'/polls/landing' )}}">\n' +
            '    <i class="fas fa-vote-yea"></i>\n' +
            '</a>')
        $('#polls').html(button);
        $('[data-toggle="tooltip"]').tooltip();
    }

    //For random student selection
    var students = Object.values(@json($course->users->reject(function($s) {return $s->instructor;})));
    function random_student() {
        var rand = students[Math.floor(Math.random() * students.length)];
        var seat = rand.pivot.seat == null ? "" : " ("+rand.pivot.seat+")";
        document.getElementById("random_text").innerHTML = rand.firstname + " " + rand.lastname + seat;
        $('#randomModal').modal('show');
    }

</script>
