@extends('layouts.instructor')

@section('links')
    <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
@stop

@section('JS')
    <script src="{{ asset('js/ractive.min.js') }}"></script>
    <script src="{{ asset('js/Sortable.min.js') }}"></script>
@endsection

@section('content')

    <div id="target"></div>

    <script id="template" type="text/ractive">
        @include('instructor.poll.landingPage')
    </script>



    <script type="text/javascript">
        const deleteURL     = "{{url('/instructor/course/'.$course->id.'/polls/delete')}}";
        const editURL      = "{{url('/instructor/course/'.$course->id.'/polls/edit')}}";
        const activateURL  = "{{url('/instructor/course/'.$course->id.'/polls/activate')}}";
        const resultsURL   = "{{url('/instructor/course/'.$course->id.'/polls/results')}}";
        const completeURL  = "{{url('/instructor/course/'.$course->id.'/polls/complete')}}";
        const restartURL   = "{{url('/instructor/course/'.$course->id.'/polls/restart')}}";
        const duplicateURL = "{{url('/instructor/course/'.$course->id.'/polls/duplicate')}}";
		const copyURL 	   = "{{url('/instructor/course/'.$course->id.'/polls/copy')}}";

        var data = {!! $data !!};
        console.log(data);

        var ractive = new Ractive({
            el: "#target",
            template: "#template",
            data: data,
            delimiters: [ '[[', ']]' ],
        });

        //Make the new polls list sortable
        var newOrder = [];
        var newList = ractive.get("new_polls");
        for(i=0;i<newList.length;i++)
            newOrder.push(newList[i].id);
        ractive.set("newOrder", newOrder);
        newList = Sortable.create(document.getElementById('new_polls'), {
            onMove:function (evt) {
                if(evt.from !== evt.to)  //Prevent moving between lists.
                    return false;
            },
            onEnd: function (evt) {
                ractive.set("newOrder", newList.toArray());
            }
        });

        ractive.on("updateOrder", function(event) {
            var data = {
                _token: "{{ csrf_token() }}",
                newOrder: ractive.get("newOrder"),
            };
            $.post("{{url('instructor/course/'.$course->id.'/polls/updateOrder')}}", data, function(resp) {
                console.log(resp);
                var n = new Noty({
                    theme: 'bootstrap-v4',
                    type: 'success',
                    layout:'centerRight',
                    text: 'Poll order updated.  Click to dismiss.',
                }).show();
            });
        });

        ractive.on('edit', function(event) {
            console.log(event);
            window.location.href = editURL + '/' + event.node.id;
        });

        ractive.on('delete', function(event) {
            var keypath = event.resolve().substring(0, event.resolve().length - 2)
            var index = event.get('@index');

            $.post(deleteURL, {_token: "{{ csrf_token() }}",id: event.node.id}, function (resp) {
                ractive.splice(keypath, index, 1);
            })
        });

        ractive.on("activate", function (event) {
            var id = event.node.id;
            if (ractive.get("in_progress").length>0) {
                $("#progress").fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100);
            }
            else {
                $.post(activateURL, {_token: "{{ csrf_token() }}",id: id}, function (resp) {
                    var new_polls = ractive.get("new_polls");
                    var index = event.node.getAttribute('data-index');
                    ractive.set("in_progress", new_polls[index]);
                    ractive.splice("new_polls", index, 1);
                    console.log(resp);
                    ractive.update();
                    window.location.href = resultsURL + '/' + id;
                });
            }
        });

        ractive.on('allow_answers', function (event) {
            console.log(event);

            //event.context.complete = "0";
            if (ractive.get("in_progress").length>0) {
                $("#progress").fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100);
            }
            else {
                var index = event.node.getAttribute('data-index');
                $.post(restartURL, {_token: "{{ csrf_token() }}",id: event.node.id}, function (data) {
                    console.log(data);
                    var completed = ractive.get("completed");
                    ractive.set("in_progress", completed[index]);
                    ractive.splice("completed", index, 1);
                    ractive.update();
                });
                window.location.href = resultsURL + '/' + event.node.id;
            }
        });

        ractive.on('complete_poll', function (event) {
            var current = ractive.get("in_progress")[0];
            current.complete = 1;
            $.post(completeURL, {_token: "{{ csrf_token() }}",id: event.node.id}, function (data) {
                console.log(data);
                var completed = ractive.get("completed");
                completed.push(current);
                ractive.set("completed",completed);
                ractive.set("in_progress", []);
                ractive.update();
            });
        });

        /*ractive.on('duplicate', function (event) {
            console.log(event);
            var index = event.node.getAttribute('data-index');
            var poll = ractive.get("completed")[index];
            var dup = JSON.parse(JSON.stringify(poll));
            var new_polls = ractive.get("new_polls");
            new_polls.push(dup);
            console.log(dup);
            $.post(duplicateURL, {_token: "{{ csrf_token() }}",poll: dup}, function (data) {
                data = JSON.parse(data);
                dup.id = data.id;
                ractive.update();
            })
        }); */

        ractive.on('duplicate', function (event) {
           console.log(event);
           var id = event.node.id;
            $.post(duplicateURL, {_token: "{{ csrf_token() }}",id: id}, function (resp) {
                console.log(JSON.parse(resp));
                var new_polls = ractive.get("new_polls");
                new_polls.push(JSON.parse(resp).poll);
                ractive.update();
            });
        });
		
        ractive.on('copy', function (event) {
            console.log(event);
            var dup = JSON.parse(JSON.stringify(event.get()));
            //var new_polls = ractive.get("new_polls");
            //new_polls.push(dup);
            console.log(dup);
			var new_course_id = ractive.get("new_course_id");
			console.log("new_course" + new_course_id);
            $.post(copyURL, {_token: "{{ csrf_token() }}", poll: dup, new_course_id : new_course_id}, function (data) {
                console.log(data);
				//data = JSON.parse(data);
                //dup.id = data.id;
                ractive.update();
            })
        });
		
		ractive.on('setCopy', function (event) {
			console.log(event);
			ractive.set("new_course_id", event.node.id);
			ractive.set("copy", 1);
			ractive.set("copy_title", event.get().name);
		});

        ractive.on("results", function (event) {
            window.location.href = resultsURL + '/' + event.node.id
        });

        //Include the name in the poll import dialog
        $('#assignment_import').on('change',function(){
            //get the file name
            var fileName = $(this).val();
            fileName = fileName.replace(/^.*\\/, "");
            //replace the "Choose a file" label
            $(this).next('.custom-file-label').html(fileName);
            console.log(fileName);
        });
        
    </script>
@stop
