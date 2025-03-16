@extends('layouts.instructor')

@section('links')
    <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
@stop

@section('JS')
    <script src="{{ asset('js/ractive.min.js') }}"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src='{{ asset('/js/moment.min.js') }}'></script>
    <link href="{{ asset('css/summernote/summernote-bs4.css') }}" rel="stylesheet">
    <script src="{{ asset('js/summernote-bs4.js') }}"></script>
    <script src="{{ asset('js/exif.js') }}"></script>
    <script src="{{ asset('js/summernote-images.js') }}"></script>
    <script src="{{ asset('js/dropzone.js') }}"></script>
    <link href="{{ asset('css/dropzone.css') }}" rel="stylesheet">
    <script src="{{ asset('js/Sortable.min.js') }}"></script>
@endsection

@section('content')

    <div id="target"></div>

    <script id="template" type="text/ractive">
        @include('instructor.files.landingPage')
    </script>

    <script type="text/javascript">
        const saveURL     = "{{url('/instructor/course/'.$course->id.'/files/saveFileLayout')}}";
        const deleteURL     = "{{url('/instructor/course/'.$course->id.'/files/deleteFile')}}";

        var data = {!! $data !!};
        console.log(data);

        var ractive = new Ractive({
            el: "#target",
            template: "#template",
            data: data,
            delimiters: [ '[[', ']]' ],
        });

        ractive.set("saving", "Save File Layout");

        Dropzone.autoDiscover = false;
        var zones=[];
        var updateZone;
        for(i=0; i < data.folders.length; i++) {
            dZone(data.folders[i].id);
            sorter(i);
        }

        function dZone(id, update=false) {
            var url = "upload/";
            var div = "div#drop_";
            if(update) {
                url = "update/";
                div = "div#update_";
            }

            var zone = new Dropzone(div+id, {
                url: url+id,
                sending: function(file, xhr, formData) {
                    formData.append("_token", "{{ csrf_token() }}");
                    if(!update) {
                        var folders = ractive.get("folders");
                        var folder = folders.find(obj => {
                            return obj.id === id
                        });
                        formData.append("order", folder.course_files.length);
                    }
                },
                success: function (file, response) {
                    if(update) {
                        var folders = ractive.get("folders");
                        var folder = folders.find(obj => {
                            return obj.id === response.file.folder_id
                        });
                        var myFile = folder.course_files.find(obj => {
                            return obj.id === response.file.id
                        });
                        index = folder.course_files.findIndex(x => x.id === id);
                        folder.course_files[index] = response.file;
                        ractive.update();
                        $('.modal').modal('hide');
                    }
                    else {
                        var folders = ractive.get("folders");
                        var folder = folders.find(obj => {
                            return obj.id === id
                        });
                        folder.course_files.push(response.file);
                        ractive.update();
                        file.previewElement.classList.add("dz-success");
                        this.removeFile(file);
                    }
                },
            });
            if(update)
                updateZone = zone;
            else {
                var folders = ractive.get("folders");
                index = folders.findIndex(x => x.id === id);
                zones[index] = zone;
            }
        }

        function sorter(i) {
            Sortable.create(document.getElementById('folder_'+i), {
                animation: 150,
                handle: ".my-handle",
                group: 'shared',
                onSort: function (evt) {
                    var order = this.toArray();
                    console.log(order);
                    var items = [];
                    order.forEach(function(id) {
                        items.push(id);
                    });
                    console.log(items);
                    ractive.set("folders["+i+"].sortedFiles", items);
                },
            });
        }

        ractive.on("addFolder", function(event) {
            var folders = this.get("folders");
            var folder = {
                id: -1,
                name: "",
                visible: true,
                options: {},
                course_files: [],
            };
            folders.push(folder);
            ractive.update();
        });

        ractive.on("removeFolder", function(event) {
            ractive.splice("folders",event.get('@index'),1);
        });

        ractive.on("moveFolder", function(event) {
            var folders = ractive.get("folders");
            var folder = event.get();
            var index = folders.indexOf(folder);

            if (event.node.id == "Down")
            {
                if (index + 1 < folders.length)
                {
                    console.log("Swapping down");
                    folders[index] = folders[index + 1];
                    folders[index + 1] = folder;
                    ractive.set('folders', folders);
                    ractive.update();
                    zones[index].destroy();
                    zones[index+1].destroy();
                    dZone(folders[index].id);
                    dZone(folders[index+1].id);
                }
            }
            else
            {
                if (index - 1 >= 0)
                {
                    console.log("Swapping up");
                    folders[index] = folders[index - 1];
                    folders[index - 1] = folder;
                    ractive.set('folders', folders);
                    ractive.update();
                }
            }

        });

        ractive.on("collapseFolder", function(event) {
            var folders = ractive.get("folders");
            var folder = event.get();
            var index = folders.indexOf(folder);
            folders[index].collapsed = true;
            ractive.update();
        });

        ractive.on("expandFolder", function(event) {
            var folders = ractive.get("folders");
            var folder = event.get();
            var index = folders.indexOf(folder);
            folders[index].collapsed = false;
            ractive.update();
        });

        ractive.on("collapseAll", function(event) {
            var folders = ractive.get("folders");
            for(i=0; i<folders.length; i++)
                folders[i].collapsed=true;
            ractive.update();
        });

        ractive.on("expandAll", function(event) {
            var folders = ractive.get("folders");
            for(i=0; i<folders.length; i++)
                folders[i].collapsed=false;
            ractive.update();
        });

        ractive.on("save", function(event) {
            ractive.set("saving", "Saving...");
            console.log(data);
            $.post(saveURL,
                {
                    _token: "{{ csrf_token() }}",
                    data: JSON.stringify(data),
                })
                .done(function (response) {
                    response = JSON.parse(response);
                    console.log(response);
                    if(response.status==="success")
                        ractive.set("saving","Saved!");
                    data.id = response.id;
                    for(i=0; i<data.folders.length; i++) {
                        if(data.folders[i].id === -1) {
                            data.folders[i].id = response.fids[i];
                            ractive.update();
                            dZone(response.fids[i]);
                            sorter(i);
                            $('.modal').on("hide.bs.modal", function (event) {
                                updateZone.destroy();
                            });
                        }
                        else
                            data.folders[i].id = response.fids[i];
                    }
                    ractive.update();
                })
                .fail(function(xhr,status,error) {  //Deal with post failure.  Suggest page reload in case it's a session timeout.
                    ractive.set("saving","Error Saving");
                    console.log(error);
                });
        });

        ractive.on("deleteFile", function(event) {
            file = event.get();
            keys = event.resolve().split('.');
            file.delMsg = true;
            ractive.update();
            folder = event.getParent().get();

            $.post(deleteURL,
                {
                    _token: "{{ csrf_token() }}",
                    fid: file.id,
                })
                .done(function (response) {
                    console.log(response);
                    index = folder.findIndex(x => x.id === file.id);
                    ractive.splice(keys[0]+'.'+keys[1]+'.'+keys[2],index,1);
                })
                .fail(function(xhr,status,error) {  //Deal with post failure.  Suggest page reload in case it's a session timeout.
                    file.delMsg = "error";
                    console.log(error);
                });
        });

        ractive.on("updateFile", function(event) {
            file = event.get();
            dZone(file.id,true);
        });

        $('.modal').on("hide.bs.modal", function (event) {
            updateZone.destroy();
        });

    </script>
@stop
