@extends('layouts.instructor')

@section('links')
    <li class="nav-item"><a class="nav-link" href="{{url('instructor/course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
@stop

@section('JS')
    <script src="{{ asset('js/ractive.min.js') }}"></script>
    <link href="{{ asset('css/summernote/summernote-bs4.css') }}" rel="stylesheet">
    <script src="{{ asset('js/summernote-bs4.js') }}"></script>
    <script src="{{ asset('js/exif.js') }}"></script>
    <script src="{{ asset('js/summernote-images.js') }}"></script>
    <script src="{{ asset('js/papaparse.min.js') }}"></script>
    <script src="{{ asset('js/Sortable.min.js') }}"></script>
    <script src="{{ asset('js/ecStat.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.1.0/dist/echarts.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mathjs/9.3.2/math.js" integrity="sha512-Imer9iTeuCPbyZUYNTKsBWsAk3m7n1vOgPsAmw4OlkGSS9qK3/WlJZg7wC/9kL7nUUOyb06AYS8DyYQV7ELEbg==" crossorigin="anonymous"></script>
@endsection

@section('content')

    <div id="target"></div>

    <script id="template" type="text/ractive">
        @include('instructor.grades.gradeLandingPage')
    </script>

    <script type="text/javascript">

        var data = {!! $data !!};

        console.log(data);

        var ractive = new Ractive({
            el: "#target",
            template: "#template",
            data: data,
            delimiters: [ '[[', ']]' ],
            computed: {
                grade_table: function() {
                    let stud = this.get("course.students");
                    let grade_groups = this.get("course.grade_groups");
                    stud.forEach(s => {
                        s.grade_items = [];
                        let i=0;
                        grade_groups.forEach((gg,ggi) => {
                            gg.items.forEach((gi,gii) => {
                                s.grade_items[i] = gi.scores.find(score => score.user_id === s.id);
                                i++;
                            });
                        });
                    });
                    return stud;
                },
                stats: function() {
                    let grade_groups = this.get("course.grade_groups");
                    let stats_collection = [];
                    grade_groups.forEach((gg, ggi) => {
                        stats_collection[ggi] = [];
                        gg.items.forEach((gi,i) => {
                            let list = gi.scores.map(x => parseFloat(x.earned));
                            let zero_length = list.length;
                            list = list.filter(x => x);
                            let bins = list.length === 0 ? [] : ecStat.histogram(list);
                            let mean = list.length > 0 ? math.round(math.mean(list),2) : 0;
                            let stdev = list.length > 0 ? math.round(math.std(list),2) : 0;
                            let median = list.length > 0 ? math.round(math.median(list),2) : 0;
                            let max = list.length > 0 ? math.round(math.max(list),2) : 0 ;
                            let min = list.length > 0 ? math.round(math.min(list),2) : 0;
                            let excluded = zero_length - list.length;
                            let basics = {num: list.length, mean: mean, stdev: stdev, median: median, max: max, min: min, excluded: excluded};
                            stats_collection[ggi][i] = {list: list, bins: bins, basics: basics};
                        });
                    });
                    return stats_collection;
                }
            }
        });

        var charts = [];

        function initHistograms() {
            let stats = ractive.get("stats");

            let options = {
                color: ['rgb(25, 183, 207)'],
                grid: {
                    left: '5%',
                    right: '3%',
                    bottom: '10%',
                    top: '3%',
                    containLabel: true
                },
                xAxis: [{
                    type: 'value',
                    scale: true,
                    name: 'Score',
                    nameLocation: 'middle',
                    nameGap: 20
                }],
                yAxis: [{
                    type: 'value',
                    name: 'Count',
                    nameLocation: 'middle',
                    nameGap: 20
                }],
                tooltip: {
                    formatter: function(params) {
                        return `Score Range: ${params.value[4]}<br />
                                Count: : ${params.data[1]} `;
                    }
                },
                series: [{
                    name: 'count',
                    type: 'bar',
                    barWidth: '99.3%',
                    label: {
                        normal: {
                            show: true,
                            position: 'insideTop',
                            formatter: function(params) {
                                return params.value[1];
                            }
                        }
                    },

                }]
            };
            stats.forEach((gg,ggi) => {
                charts[ggi] = [];
                gg.forEach((gi,gii) => {
                    charts[ggi][gii] =  echarts.init(document.getElementById("chart_"+ggi+"_"+gii));
                    options.series[0].data = gi.bins.data;
                    charts[ggi][gii].setOption(options);
                });
            });
        }

        ractive.on("toggle_stats", function(context) {
            this.toggle("show_stats");
            resize();
        });

        $(window).on('resize', resize);  //Resize charts.
        // Resize function
        function resize() {
            setTimeout(function () {
                // Resize charts
                charts.forEach(g => g.forEach(i => i.resize()));
            }, 200);
        }

        initHistograms();

        ractive.set("save_msg","Save");

        for(var i=0; i<data.course.grade_groups.length; i++) {
            groupEditor(i,data.course.grade_groups[i].comments);
            sorter(data.course.grade_groups[i].id);
        }

        //Make the grade items sortable
        function sorter(id) {
            console.log("sortable",id);
            Sortable.create(document.getElementById('group_'+id), {
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
                    var index = data.course.grade_groups.findIndex(p => p.id === id);
                    console.log(index);
                    ractive.set("course.grade_groups["+index+"].sortedGrades", items);
                },
            });
        }

        ractive.on("add_grade_group", function(event) {
           let course = ractive.get("course");
           let grade_groups = course.grade_groups;
           group = {
               id: -1,
               course_id: course.id,
               title: "Grade Group",
               items: [],
               options: null,
               order: grade_groups.length,
               comments: "",
               visible: 1,
           };
           grade_groups.push(group);
           ractive.update();
        });

        ractive.on("parse_csv", function(event) {
            var csvString = ractive.get("csvString");
            var parsed = Papa.parse(csvString, {header: true, skipEmptyLines: 'greedy'});
            var selected = [];
            var headings = {};
            ractive.set("preview",true);
            ractive.set("preview_data",parsed.data);
            console.log(parsed);
            assign_items(parsed);
        });

        function groupEditor(id, text) {
            var div = '#comments_' + (id);
            console.log("init " + div);
            $(div).summernote({
                placeholder: 'Grade group comments...',
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
                        summernoteOnImageUpload(files,div);
                    },
                    onChange: function(contents, $editable) {
                        ractive.set("course.grade_groups["+id+"].comments", contents)
                    }
                }
            });
            $(div).summernote('code',text)
        }

        function assign_items(parsed) {
            var fields = parsed.meta.fields;
            for(i=0; i<fields.length; i++) {
                if(fields[i] == "Email")
                    continue;
                if(!item_exists(fields[i], parsed.data)) {
                    create_item(fields[i], parsed.data);
                }
            }
            ractive.update();
        }

        function item_exists(title, scores) {
            var course = ractive.get("course");
            for(var i=0; i<course.grade_groups.length; i++) {
                if(!course.grade_groups[i].hasOwnProperty("items"))
                    continue;
                for(var j=0; j<course.grade_groups[i].items.length; j++) {
                    if(course.grade_groups[i].items[j].title === title) {
                        update_grades(title, course.grade_groups[i].items[j], scores);
                        return true;
                    }
                }
            }
            console.log(title + " not found in existing grade items.");
            return false;
        }

        function update_grades(title, item, scores) {
            for(var i=0; i<scores.length; i++) {
                var email = scores[i]["Email"];
                var user_id = data.student_ids[email];
                if(user_id === undefined) {
                    console.log("Cannot find "+email);
                    continue;
                }

                var index2 = item.scores.findIndex(p => p.user_id === user_id);
                if(index2 === -1) {
                    index2 = item.scores.length;
                    item.scores.push({
                        id: -1,
                        user_id: user_id,
                    });
                }
                item.scores[index2].earned = scores[i][title];
                item.scores[index2].viewed = 0;
            }
        }

        function create_item(title, scores) {
            var index = data.course.grade_groups.findIndex(p => p.title === "Default");
            if(index === -1) {
                data.course.grade_groups.push({
                    title: "Default",
                    id: -1,
                    order: data.course.grade_groups.length-1,
                    visible: 1,
                    items: [],
                    comments: "",
                });
                index = data.course.grade_groups.length-1;
                ractive.update();
                groupEditor(index,"");
            }

            var item = {
                title: title,
                id: -1,
                order: data.course.grade_groups[index].items.length-1,
                visible: 1,
                scores: [],
                possible: 100,
                comments: "",
            };

            for(var i=0; i<scores.length; i++) {
                var email = scores[i]["Email"];
                var user_id = data.student_ids[email];
                if(user_id === undefined) {
                    console.log("Cannot find "+email);
                    continue;
                }

                var index2 = item.scores.findIndex(p => p.user_id === user_id);
                if(index2 === -1) {
                    index2 = item.scores.length;
                    item.scores.push({
                        id: -1,
                        user_id: user_id,
                    });
                }
                item.scores[index2].earned = scores[i][title];
                item.scores[index2].viewed = 0;
            }

            data.course.grade_groups[index].items.push(item);
        }

        ractive.on("toggleVisible", function(event) {
            console.log(event.get().visible);
            event.get().visible = event.get().visible == 1 ? 0 : 1;
            ractive.update();
        });

        ractive.on("removeItem", function(event) {
            console.log(event.getParent().resolve());
            console.log(event.get());
            //ractive.splice(event.getParent().resolve(),event.get('@index'),1);
            event.get().deleted = true;
            ractive.update();
        });

        ractive.on("moveItemGroup", function(context, i, g, g2) {
            let course = ractive.get("course");
            let groups = course.grade_groups;

            let item = groups[g].items[i];

            console.log(item);

            groups[g2].items.push(item);

            groups[g].items.splice(i,1);

            groups[g].items.forEach((obj, index) => {
                obj.order = index;
            });

            groups[g2].items.forEach((obj, index) => {
                obj.order = index;
            });

            ractive.update();
        });

        ractive.on("save_grades", function(event) {
            ractive.set("save_msg","Saving...");

            $.post('saveGrades',
                {
                    _token: "{{ csrf_token() }}",
                    grade_groups: JSON.stringify(data.course.grade_groups),
                })
                .done(function (response) {
                    console.log(response);
                    ractive.set("course",response.course);
                    ractive.set("save_msg","Saved");
                    ractive.set("unsavedChanges",false);
                    ractive.update();
                    for(var i=0; i<data.course.grade_groups.length; i++) {
                        groupEditor(i,data.course.grade_groups[i].comments);
                        sorter(data.course.grade_groups[i].id);
                    }
                })
                .fail(function(xhr,status,error) {  //Deal with post failure.
                    ractive.set("save_msg","Error Saving");
                    console.log(error);
                });
        });


    </script>
@stop
