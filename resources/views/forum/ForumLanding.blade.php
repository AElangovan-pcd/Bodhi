@extends($instructor ? 'layouts.instructor' : 'layouts.student')

@if($instructor == 1)
@section('links')
	<li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
@stop

@else
@section('links')
	<li class="nav-item"><a class="nav-link" href="{{url('course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
@stop
@endif

@section('JS')
	<script type="text/javascript" src="{{asset('js/ractive.min.js')}}"></script>
	<script src='{{ asset('/js/moment.min.js') }}'></script>
@stop

@section('css')
	<link href="{{asset('/css/bootstrap-toggle.min.css')}}" rel="stylesheet">
    <style>
        a.card,
        a.card:hover {
            color: inherit;
            text-decoration: none;
        }
    </style>
@stop

@section('content')
	<div id="landing">
		<script type="text/ractive" id="template">
			@include('forum.forumLandingPage')
		</script>
	</div>

	<script type="text/javascript">
        var data = {!! $data !!};
        console.log(data);
        const subscriptionURL = "{{url('course/'.$course->id.'/forum/forum_subscription')}}";

        course_socket
            .listen('NewForumAnswer', (e) => {
                console.log(e);
                var n = new Noty({
                    theme: 'bootstrap-v4',
                    type: 'information',
                    layout:'bottomRight',
                    text: '<strong>New Forum Response</strong><br>There is a new response in the forum \''+e.forum_title+'\'.',
                    buttons: [
                        Noty.button('Go!', 'btn btn-success mr-2', function () {
                            console.log("load pressed");
                            window.location.href = 'view/' + e.forum_id;
                            n.close();
                        }),
                        Noty.button('Ignore', 'btn btn-danger', function() {
                            console.log("no pressed");
                            n.close();
                        })
                    ]
                }).show();
                update_new_count(e.forum_id);
            })
            .listen('NewForumTopic', (e) => {
                console.log(e);
                var n = new Noty({
                    theme: 'bootstrap-v4',
                    type: 'information',
                    layout:'bottomRight',
                    text: '<strong>New Forum Topic</strong><br>\''+e.forum_title+'\'',
                    buttons: [
                        Noty.button('Go!', 'btn btn-success mr-2', function () {
                            console.log("load pressed");
                            window.location.href = 'view/' + e.forum_id;
                            n.close();
                        }),
                        Noty.button('Ignore', 'btn btn-danger', function() {
                            console.log("no pressed");
                            n.close();
                        })
                    ]
                }).show();
            })
        ;

        function update_new_count(id) {
            var forums = ractive.get("forums");
            var index = forums.findIndex(x => x.id==id);
            console.log(index);
            forums[index].newResponses++;
            forums[index].new_activity = true;
            ractive.set("forums",forums);
        }

        var ractive = new Ractive({
            el: "#landing",
            template: "#template",
            data: data,
            delimiters: [ '[[', ']]' ],
            computed: {
                newActivity: function() {
                    let forums = this.get("forums");
                    return forums.filter(x=>x.new_activity).length;
                }
            }
        });

        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        });

        ractive.set("update", "Update Subscription");

        ractive.on("subscribe", function(event) {
            ractive.toggle("subscribed");
        });

        ractive.on("autosubscribe", function(event) {
            ractive.toggle("autosubscribed");
        });

        ractive.on("updateSubscription", function(event) {
            console.log(event);
            var data = {
                _token: "{{ csrf_token() }}",
                course_id: ractive.get("course").id,
                subscribe: ractive.get("subscribed"),
                autosubscribe: ractive.get("autosubscribed"),
            }
            console.log(data);
            ractive.set("update","Updating...");
            $.post(subscriptionURL, data, function(resp) {
                console.log(resp);
                ractive.set("update","Subscription Updated");
            });
        });

        ractive.on("sort", function(context, sortBy, reverse) {
            let forums = sortForums(sortBy, ractive.get("forums"), reverse);
            ractive.set("forums",forums);
            MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
            ractive.set("sortedBy", sortBy);
        });

        function sortForums(sortBy, forums, reverse = false) {
            console.log(reverse);
            if(forums == null)
                return;
            forums.sort(function(a,b) {
                return a[sortBy] > b[sortBy] ? -1 : 1;
            });

            if(reverse)
                forums = forums.reverse();
            return forums;
        }

        var forums = ractive.get("forums");

        function updateTimes() {
            if(forums == null)
                return;
            var forums = ractive.get("forums");
            for (var i=0, len=forums.length; i<len; i++) {
                forums[i].time = moment(forums[i].created_at).fromNow();
            }
            ractive.set("forums",forums);
        }

        updateTimes();
        setInterval(function(){
            updateTimes();
        },60000);

	</script>

@stop
