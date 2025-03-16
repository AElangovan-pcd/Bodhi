@extends('layouts.instructor')

@section('links')
    <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
    <li class="nav-item"><a class="nav-link" id="pollLanding" href="{{url('instructor/course/'.$course->id.'/polls/landing')}}">Polls</a></li>
@stop

@section('JS')
    <script src="{{ asset('js/ractive.min.js') }}"></script>
    <link href="{{ asset('css/summernote/summernote-bs4.css') }}" rel="stylesheet">
    <script src="{{ asset('js/summernote-bs4.js') }}"></script>
    <script src="{{ asset('js/exif.js') }}"></script>
    <script src="{{ asset('js/summernote-images.js') }}"></script>
@stop

@section('content')

    <div id="target"></div>

    <script id="template" type="text/ractive">
        @include('instructor.poll.createPollPage')
    </script>

    <script type="text/javascript">
        var data = {!! $data !!};
        const saveURL = "{{url('/instructor/course/'.$course->id.'/polls/save')}}";
        data.saving = "Save Poll";

        var alphabet = [];  // array with all caps
        var alpha_bin = 0;
        for (var i = 65; i < 91; i++)
            alphabet.push(String.fromCharCode(i))

        for (var i = 0; i < data.choices.length; i++) {
            if (data.choices[i].length == 1 && isNaN(data.choices[i])) {
                console.log(data.choices[i]);
                toggle_alpha(alphabet.indexOf(data.choices[i]))
            }
            data.choices[i] = {
                text: data.choices[i],
            }
        }

        console.log(data);

        var ractive = new Ractive({
            el       : "#target",
            template : "#template",
            data     : data,
			delimiters: [ '[[', ']]' ]
        });


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

        function next_alpha() {
            let op = 1;
            let index = 0;
            while ((alpha_bin & op) >> index == 1) {
                op <<= 1;
                index++;
            }
            return index;
        }

        function toggle_alpha(index) {
            if (index > -1 && index < 25)
                alpha_bin ^= 1 << index;
        }

        function checkBorder(length) {
            if (length > 6) {
                $("#adding").removeClass("border-right");
                $("#alternatives").addClass("border-left");
                $("#save").removeClass("col-sm-offset-4");
                $("#save").addClass("col-sm-offset-7");
            }
            else {
                $("#adding").addClass("border-right");
                $("#alternatives").removeClass("border-left");
                $("#save").removeClass("col-sm-offset-7");
                $("#save").addClass("col-sm-offset-4");
            }
            if (length > 0)
                ractive.set("ready", true);
            else
                ractive.set("ready", false);
        }

        ractive.on("addChoice", function (event) {
            console.log(event);
            let text, moveFocus;
            if (event.original.type == "click")
                text = event.node.previousElementSibling.value;
            else if (event.original.charCode == 13)
                if (event.node.id == "mainAdd")
                    text = event.context.new_choice;
                else {
                    text = ""
                    moveFocus = true;
                }
            else
                return;

            if (text.length === 0) {
                let index = next_alpha();
                text = alphabet[index];
                toggle_alpha(index); // this letter is chosen
            }

            let choices = ractive.get('choices');
            let choice = {
                text: text
            }
            choices.push(choice);
            ractive.set("new_choice", "");
            ractive.update('choices', choices);
            if (moveFocus) {
                $("#choice_" + (event.index.c + 1)).focus();
            }
            checkBorder(choices.length);
        });
		
		ractive.on("drawOptions", function(event) {
			ractive.set("customFlag",0);
			var choices = ractive.get("choices");
			let option = event.node.id;
			if(option == "blank")
			    ractive.set("background","data:image/gif;base64,R0lGODlhAQABAAAAACwAAAAAAQABAAA=");
            else if(option == "axes")
                ractive.set("background","data:image/png;base64,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");

			ractive.set("drawMessage","Background selected as " + option);
			ractive.set("ready", true);
		});
		
		function readFile() {
			if (this.files && this.files[0]) {
				var FR= new FileReader();
				var image = ractive.get("image");
				FR.readAsDataURL( this.files[0] );
				FR.onload = function() {
					image={text: FR.result};
					console.log(image);
					ractive.set("customFlag2",1);
					ractive.set("background",FR.result);
					ractive.set("ready", true);
				}
			}
		}
		
		
		
		ractive.on("setCustom", function(event) {
			var choices = ractive.get("choices");
			let bg = document.getElementById('customURL').value;
			choices[0]={text: bg};
			console.log(choices);
			ractive.set("customFlag2",1);
			ractive.set("background",bg);
			ractive.set("ready", true);
		});
		
		ractive.on("customBG", function(event) {
			ractive.set("customFlag",1);
			ractive.set("drawMessage","Background selected as custom.  Upload an image to use as the background.  Note that square images work best. A preview should appear after upload.");
			document.getElementById("upload").addEventListener("change", readFile);
		});

        ractive.on("quickAdd", function (event) {
            let howmany = event.node.id;
            var choices = ractive.get("choices");
            while (howmany > 0) {
                let index = next_alpha();
                text = alphabet[index];
                toggle_alpha(index); // this letter is chosen
                choices.push({text: text});
                howmany--;
            }
            ractive.set("choices", choices);
            checkBorder(choices.length);
        });
		
		ractive.on("changeType", function (event) {
			var type = ractive.get("poll_type");
			if (type == 0) {
				ractive.set("poll_type",1);
				ractive.set("ready", true);
			}
			else if (type ==1) {
				ractive.set("poll_type",2);
				var choices = ractive.get("choices");
				checkBorder(choices.length);
			}
			else {
				ractive.set("poll_type",0);
				var choices = ractive.get("choices");
				checkBorder(choices.length);
			}
			
		});

        ractive.on("removeChoice", function (event) {
            console.log(event);
            var choices = ractive.get('choices');
            let index = event.node.id;
            let a_index = alphabet.indexOf(choices[index].text);
            if (a_index > -1) {
                toggle_alpha(a_index);
            }
            ractive.splice("choices", index, 1);
            checkBorder(ractive.get('choices').length);
        });

        ractive.on("save", function (event) {
            console.log(event);
            if (event.original.charCode)
                if (event.original.charCode != 13 && event.original.charCode != 32) { // return, space
                    console.log("nope");
                    return;
                }
				
			var content = $('#editor').summernote('code');

			ractive.set("poll_question",content);			

            if (ractive.get("poll_name").length == 0) {
                ractive.set("saving", "Please add a name")
                return false;
            }
			
            console.log("saving");
            ractive.set("saving", "Saving!")

            var data = {
                _token: "{{ csrf_token() }}",
                question: ractive.get("poll_question"),
                name: ractive.get("poll_name"),
                alternatives: ractive.get('choices'),
                image: ractive.get('background'),
                course: ractive.get("course"),
                poll_id: ractive.get("poll_id"),
				pollType: ractive.get("poll_type")
            }
			
			console.log(data);
			
            $.post(saveURL, data, function (resp) {
                ractive.animate("saving", "Saved")
                ractive.set("poll_id",resp);
                var elmnt = document.getElementById("activate");
                elmnt.scrollIntoView();
            });

        });

        ractive.on("activate", function(event) {
            ractive.set("activeMsg",null)
            var id = ractive.get("poll_id");
            $.post("{{url("/instructor/course/$course->id/polls/activate/")}}", {_token: "{{ csrf_token() }}",id: id}, function (resp) {
                console.log(resp);
                if(resp=="fail")
                    ractive.set("activateMsg","There is a poll active already.  Please close it first.");
                else
                    window.location.href = "{{url("/instructor/course/$course->id/polls/results/")}}"+"/"+id;
            });
        });
    </script>
@stop
