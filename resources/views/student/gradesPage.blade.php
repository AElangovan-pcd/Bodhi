<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-success" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            [[#grade_groups:g]]
            <div class="card">
                <div class="card-header">[[title]]</div>
                <div class="card-body">
                    <div class='col-md-12'>
                        <div id="comments_[[g]]">[[[comments]]]</div>
                    </div>
                    [[#items:i]]
                    <div class="card">
                        <div class="card-header">[[title]]</div>
                        <div class="card-body">
                            <div class='col-md-12'>
                                <div id="description_[[g]]_[[i]]">[[[comments]]]</div>
                            </div>
                            [[scores[0].earned]]
                            <br/>
                            [[#options.stats]]
                            <button class="btn btn-sm btn-info mt-3" role="button" on-click="['toggle_stats',g,i]">[[show_stats ? 'Hide' : 'Show']] Class Statistics</button>
                            <div class="row [[show_stats ? '' : 'd-none']]">
                                <div class="col-md-6">
                                    <div id="chart_[[g]]_[[i]]"  style="width:100%; height:200px;"></div>
                                </div>
                                <div class="col-md-6 my-auto">
                                    <ul>
                                        <li>N: [[~/stats[g][i].basics.num]]</li>
                                        <li>Mean: [[~/stats[g][i].basics.mean]]</li>
                                        <li>Std. Dev.: [[~/stats[g][i].basics.stdev]]</li>
                                        <li>Median: [[~/stats[g][i].basics.median]]</li>
                                    </ul>
                                </div>
                            </div>
                            [[/options.stats]]
                        </div>
                    </div>
                    [[/items]]
                </div>
            </div>
            [[/grade_groups]]
        </div>
    </div>
</div>
