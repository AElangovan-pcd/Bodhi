@extends($instructor ? 'layouts.instructor' : 'layouts.student')

@if($instructor == 1)
@section('links')
	<li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
	<li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/forum/landing')}}">Forum</a></li>
@stop

@else
@section('links')
	<li class="nav-item"><a class="nav-link" href="{{url('course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
	<li class="nav-item"><a class="nav-link" href="{{url('course/'.$course->id.'/forum/landing')}}">Forum</a></li>
@stop
@endif

@section('JS')
	<script type="text/javascript" src="{{asset('js/ractive.min.js')}}"></script>
	<script src='{{ asset('/js/moment.min.js') }}'></script>
    <link href="{{ asset('css/summernote/summernote-bs4.css') }}" rel="stylesheet">
    <script src="{{ asset('js/summernote-bs4.js') }}"></script>
    <script src="{{ asset('js/exif.js') }}"></script>
    <script src="{{ asset('js/summernote-images.js') }}"></script>
@stop

@section('css')
	<link href="{{asset('/css/bootstrap-toggle.min.css')}}" rel="stylesheet">

@stop

@section('content')
	<div id="forum">
		<script type="text/ractive" id="template">
			@include('forum.viewForumPage')
		</script>
	</div>
	
	<script type="text/javascript">
		var data = {!! $data !!};
		console.log(data);
		const saveURL = "{{url('course/'.$course->id.'/forum/save_forum_answer')}}";
		const responseVoteURL = "{{url('course/'.$course->id.'/forum/response_vote')}}";
		const responseDetailsURL = "{{url('course/'.$course->id.'/forum/response_details')}}";
		const endorseURL = "{{url('instructor/course/'.$course->id.'/forum/endorse')}}";
		const subscriptionURL = "{{url('course/'.$course->id.'/forum/topic_subscription')}}";
		
		data.saving = "Post Response";
		data.responseUpdate = "Update Response";
		
		var ractive = new Ractive({
            el: "#forum",
            template: "#template",
            data: data,
			delimiters: [ '[[', ']]' ]
        });
		
		ractive.set("sortBy", "oldest");
		var responses = ractive.get("responses");
		sortResponses(ractive.get("sortBy"),responses);
		
		function updateTimes() {
			var responses = ractive.get("responses");
			for (var i=0, len=responses.length; i<len; i++) {
				responses[i].time = moment(responses[i].created_at).fromNow();
			}
			ractive.set("responses",responses);
			
			var forum_time = ractive.get("forum").created_at;
			ractive.set("forum.time",moment(forum_time).fromNow());			
		}
		
		updateTimes();
			setInterval(function(){
		updateTimes();
		},60000);
		
		ractive.on("check", function(event) {
			ractive.toggle("anonymous");
		});
		
		ractive.on("editResponse", function(event) {
			var responses = ractive.get("responses");
			for (var i=0, len=responses.length; i<len; i++) {
				responses[i].editing = 0;
				if(i == event.node.id)
					responses[i].editing = 1;
			}
			ractive.set("responses", responses);
            $('#editor2').summernote({
                placeholder: 'Assignment description that students will see.',
                height: 100,
                toolbar: [
                    ["style"],
                    ["style", ["bold", "italic", "underline", "clear"]],
                    ["font", ["strikethrough", "superscript", "subscript"]],
                    ["fontsize", ["fontsize"]],
                    ["color", ["color"]],
                    ["para", ["ul", "ol", "paragraph"]],
                    ["picture"],
                    ["link"],
                    ["codeview"],
                    ["fullscreen"],
                    ["help"]
                ],
                callbacks: {
                    onImageUpload: function(files) {
                        summernoteOnImageUpload(files,"#editor2");
                    },
                }
            });
		});
		
		ractive.on("insertEqn", function(event) {
            $('#editor').summernote("insertText", event.node.id);
		});
		
		ractive.on("cancelEdit", function(event) {
			var str = "responses["+event.node.id+"].editing";
			ractive.set(str,0);
		});
		
		ractive.on("additional", function(event) {
			ractive.set("answer","");
			ractive.set("answering",1);
			ractive.set("Post Response");
            $('#editor').summernote({
                placeholder: 'Assignment description that students will see.',
                height: 100,
                toolbar: [
                    ["style"],
                    ["style", ["bold", "italic", "underline", "clear"]],
                    ["font", ["strikethrough", "superscript", "subscript"]],
                    ["fontsize", ["fontsize"]],
                    ["color", ["color"]],
                    ["para", ["ul", "ol", "paragraph"]],
                    ["picture"],
                    ["link"],
                    ["codeview"],
                    ["fullscreen"],
                    ["help"]
                ],
                callbacks: {
                    onImageUpload: function(files) {
                        summernoteOnImageUpload(files,"#editor");
                    },
                }
            });
			$('html, body').animate({
					scrollTop: $("#responsePanel").offset().top  //Scroll to the response window.
			}, 200);
			$('[data-toggle="tooltip"]').tooltip();
			MathJax.Hub.Queue(["Typeset",MathJax.Hub]);  //Force MathJax rendering.
		});
		
        ractive.on("save", function (event) {
            console.log(event);
            if (event.original.charCode)
                if (event.original.charCode != 13 && event.original.charCode != 32) { // return, space
                    console.log("nope");
                    return;
                }
			
			var answer = $('#editor').summernote('code');
			ractive.set("answer",answer);
			
            if (answer == "<p><br></p>") {
                ractive.set("saving", "Please enter an answer.")
                return false;
            }
			
            console.log("saving");
            ractive.set("saving", "Saving!");
			
			var anonymous=0;
			var anon = ractive.get("anonymous");
			if (anon == true)
				anonymous=1;

			var postKey = Math.random().toString(36).substring(7);  //To know I was the poster
			ractive.set("postKey", postKey);

            var data = {
                _token: "{{ csrf_token() }}",
				forum_answer_id : -1,
                forum_id: ractive.get("forum.id"),
				postKey: postKey,
				answer: answer,
				anonymous: anonymous,
				update: 0
            }
			
			console.log(data);

            $.post(saveURL, data, function (resp) {
				//ractive.animate("saving", "Saved");
				console.log(resp);
				ractive.set("answering",0);
            });
			ractive.set("saving", "Post Response");
        });	
		
		ractive.on("updateResponse", function (event) {
			var answer = $('#editor2').summernote('code');
			
            if (answer == "<p><br></p>") {
                ractive.set("responseUpdate", "Response cannot be blank.")
                return false;
            }
			
			var data = {
                _token: "{{ csrf_token() }}",
				forum_answer_id : event.get().id,
				forum_id: ractive.get("forum.id"),
				answer: answer,
				anonymous: event.get().anonymous,
				update: true
			}

            $.post(saveURL, data, function (resp) {
				ractive.animate("updatedResponse", "Updated");
				console.log(resp);
                var responses = ractive.get("responses");
                responses[event.node.id] = resp;
                ractive.set("responses", responses);
                ractive.update();
                $('#answer_'+event.node.id).html(resp.answer);
            });			
		});
		
		ractive.on("subscribe", function(event) {
			console.log(event);
			var data = {
                _token: "{{ csrf_token() }}",
				forum_id: event.get().forum.id,
			}
			$.post(subscriptionURL, data, function(resp) {
				console.log(resp);
				ractive.toggle("subscribed");
			});
		});

		ractive.on("responseVote", function(event) {
			var data = {
                _token: "{{ csrf_token() }}",
				forum_answer_id : event.get().id,
			}
			
			$.post(responseVoteURL, data, function(resp) {
				console.log(resp);
			});
		});
		
		ractive.on("endorse", function(event) {
			responses = ractive.get("responses");
			console.log(responses[event.node.id]);
		    var data = {
                _token: "{{ csrf_token() }}",
				forum_answer_id : responses[event.node.id].id,
			}
			
			$.post(endorseURL, data, function(resp) {
				console.log(resp);
			});
		});
		
		ractive.on("sort", function(event) {
			console.log(event);
			var sortBy = event.node.id;
			ractive.set("sortBy",sortBy);
			var responses = ractive.get("responses");
			sortResponses(sortBy, responses);
			$('[data-toggle="tooltip"]').tooltip('dispose');
			$('[data-toggle="tooltip"]').tooltip('enable');  //Fix tooltips after sorting.
		});
		
		function sortResponses(sortBy, responses) {
			var reverse = 0;
			if (sortBy == "oldest") {
				reverse = 1;
				sortBy = "created_at";
			}
			responses.sort(function(a,b) {
				return a[sortBy] > b[sortBy] ? -1 : 1;
			});
			if(reverse)
				responses = responses.reverse();
			ractive.set("responses",responses);
			for(i=0;i<responses.length;i++) {
                $('#answer_' + i).html(responses[i].answer);  //Deal with answer getting severed from ractive object
			}

			MathJax.Hub.Queue(["Typeset",MathJax.Hub]);

		}

		$(document).ready(function(){
			$('[data-toggle="tooltip"]').tooltip(); 
			if ('ontouchstart' in document.documentElement) //Fix tooltip not dismissing on mobile.
				$('body').css('cursor', 'pointer');
		})

        course_socket
			.listen('NewForumAnswer', (e) => {
            	console.log(e);
            	if(e.forum_id != ractive.get("forum").id)
            	    return;  //Bail if the new forum answer doesn't match this topic.
				if(e.postKey == ractive.get("postKey")) {  //This was my post; just load it without asking
				    console.log("my response "+e.postKey + " "+ractive.get("postKey"));
                    load_response(e.forum_answer_id);
                    return;
                }
                var n = new Noty({
                    theme: 'bootstrap-v4',
                    type: 'information',
                    layout:'bottomRight',
                    text: 'Someone has posted a new response.  Would you like to load it?',
                    buttons: [
                        Noty.button('Load', 'btn btn-success mr-2', function () {
                            console.log("load pressed");
                            load_response(e.forum_answer_id);
                            n.close();
                        }),
                        Noty.button('Ignore', 'btn btn-danger', function() {
                            console.log("no pressed");
                            n.close();
                        })
                    ]
                }).show();
        	})
			.listen('UpdatedForumAnswer', (e) => {
			    console.log(e);
                if(e.forum_id != ractive.get("forum").id)
                    return;  //Bail if the new forum answer doesn't match this topic.
				update_response(e.forum_answer_id);  //TODO change event to contain relevant info and don't require a new query
			})
			;

		function load_response(id) {
		    var postData = {
                _token: "{{ csrf_token() }}",
				forum_answer_id: id,
			};
            $.post(responseDetailsURL, postData, function(resp) {
                resp = JSON.parse(resp);
                console.log(resp);
                var responses = ractive.get("responses");
                responses.push(resp);
                ractive.set("responses",responses);
                updateTimes();
                MathJax.Hub.Queue(["Typeset",MathJax.Hub]);  //Force MathJax rendering.
                sortResponses(ractive.get("sortBy"),responses);
                ractive.update();
            });
		}

		function update_response(id) {
            var postData = {
                _token: "{{ csrf_token() }}",
                forum_answer_id: id,
            }
            $.post(responseDetailsURL, postData, function(resp) {
                resp = JSON.parse(resp);
                console.log(resp);
                var responses = ractive.get("responses");
                var index = responses.findIndex(x => x.id==id);
                responses[index]=resp;
                ractive.set("responses",responses);
                updateTimes();
                MathJax.Hub.Queue(["Typeset",MathJax.Hub]);  //Force MathJax rendering.
                sortResponses(ractive.get("sortBy"),responses);
                ractive.update();
            });
		}
		
		/*var conn = new ab.Session(socketServer,		//create Autobahn session to talk to websocket
			function() {

				conn.subscribe("forum_" + data.forum.id, function(topic, data) {
					console.log(data);
					if (data.action === "answer") {
						$.post(responseDetailsURL, data, function(resp) {
							console.log(resp);
							var responses = ractive.get("responses");
							responses.push(resp);
							ractive.set("responses",responses);
							updateTimes();
							MathJax.Hub.Queue(["Typeset",MathJax.Hub]);  //Force MathJax rendering.
							sortResponses(ractive.get("sortBy"),responses);
						});
					}
					else if (data.action == "updateAnswer") {
						$.post(responseDetailsURL, data, function(resp) {
							console.log(resp);
							var responses = ractive.get("responses");
							var index = responses.findIndex(x => x.id==data.forum_answer_id);
							responses[index]=resp;
							ractive.set("responses",responses);
							updateTimes();
							MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
							sortResponses(ractive.get("sortBy"),responses);
						});
					}
					else if (data.action == "delete_response") {
						var responses = ractive.get("responses");
						var index = responses.findIndex(x => x.id==data.forum_answer_id);
						if(index != -1)
							responses.splice(index,1);
					}						
				});
			},
			function(){
				console.warn("WS connection closed");
			},
			{'skipSubprotocolCheck': true}
		); */
		
	</script>

@stop
