@extends('layouts.student')

@section('links')
    <li class="nav-item"><a class="nav-link" href="{{url('course/'.$course->id.'/landing')}}">{{$course->name}}</a></li>
@stop

@section('JS')
    <script src="{{ asset('js/ractive.min.js') }}"></script>
    <link href="{{ asset('css/summernote/summernote-bs4.css') }}" rel="stylesheet">
    <script src="{{ asset('js/summernote-bs4.js') }}"></script>
    <script src="{{ asset('js/exif.js') }}"></script>
    <script src="{{ asset('js/summernote-images.js') }}"></script>
    <script src="{{ asset('js/papaparse.min.js') }}"></script>
    <script src="{{ asset('js/ecStat.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.1.0/dist/echarts.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mathjs/9.3.2/math.js" integrity="sha512-Imer9iTeuCPbyZUYNTKsBWsAk3m7n1vOgPsAmw4OlkGSS9qK3/WlJZg7wC/9kL7nUUOyb06AYS8DyYQV7ELEbg==" crossorigin="anonymous"></script>
@endsection

@section('content')

    <div id="target"></div>

    <script id="template" type="text/ractive">
        @include('student.gradesPage')
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
                stats: function() {
                    let grade_groups = this.get("grade_groups");
                    let stats_collection = [];
                    grade_groups.forEach((gg, ggi) => {
                        stats_collection[ggi] = [];
                        gg.items.forEach((gi,i) => {
                            if(gi.stats_scores !== undefined) {
                                let list = gi.stats_scores.map(x => parseFloat(x.earned));
                                let zero_length = list.length;
                                list = list.filter(x => x);
                                let bins = list.length === 0 ? [] : ecStat.histogram(list);
                                let mean = list.length > 0 ? math.round(math.mean(list), 2) : 0;
                                let stdev = list.length > 0 ? math.round(math.std(list), 2) : 0;
                                let median = list.length > 0 ? math.round(math.median(list), 2) : 0;
                                let max = list.length > 0 ? math.round(math.max(list), 2) : 0;
                                let min = list.length > 0 ? math.round(math.min(list), 2) : 0;
                                let excluded = zero_length - list.length;
                                let basics = {
                                    num: list.length,
                                    mean: mean,
                                    stdev: stdev,
                                    median: median,
                                    max: max,
                                    min: min,
                                    excluded: excluded
                                };
                                stats_collection[ggi][i] = {valid: true, list: list, bins: bins, basics: basics};
                            }
                            else
                                stats_collection[ggi][i] = {valid: false};
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
                    if(gi.valid === false)
                        return;
                    charts[ggi][gii] =  echarts.init(document.getElementById("chart_"+ggi+"_"+gii));
                    options.series[0].data = gi.bins.data;
                    charts[ggi][gii].setOption(options);
                });
            });
        }

        $(window).on('resize', resize);  //Resize charts.
        // Resize function
        function resize() {
            setTimeout(function () {
                // Resize charts
                charts.forEach(g => g.forEach(i => i.resize()));
            }, 200);
        }

        initHistograms();

        ractive.on("toggle_stats", function(context, g, i) {
            this.toggle('grade_groups.'+g+'.items.'+i+'.show_stats');
            resize();
        });

    </script>
@stop
