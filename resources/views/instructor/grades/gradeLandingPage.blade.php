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

            <h3>Grade Landing Page for [[course.name]]</h3>

            <div class="alert alert-warning">This is a new experimental feature that is not complete and has no documentation.  If you want to pilot this feature, you are encouraged to talk with Greg first.</div>

            <div class="card-deck">
                <div class="card">
                    <div class="card-body">
                        <textarea class="form-control" rows="3" id="csvBox" placeholder="Paste grade data">[[csvString]]</textarea>
                        <button class="btn btn-default" on-click="parse_csv">Preview</button>
                        <button class="btn btn-default" on-click="save_grades">[[save_msg]]</button>
                    </div>
                </div>
            </div>
                <button class="btn btn-sm btn-info" on-click="toggle_stats">Toggle Stats</button>
                <button class="btn btn-sm btn-success" on-click="add_grade_group">Add Grade Group</button>

            [[#course.grade_groups:g]]
            [[#deleted != true]]
            <div class="card">
                <div class="card-header">
                    <span contenteditable="true" value="[[title]]"></span>
                    <div class="btn-toolbar float-right" role="toolbar" aria-label="Toolbar with button groups">
                        <div class="btn-group mr-2" role="group">
                            <button class="btn btn-sm btn-outline-secondary" on-click="toggleVisible" id="[[i]]" type="button" data-toggle="tooltip" data-placement="top" title="Toggle Visibility"><i class="[[visible ? 'far fa-eye' : 'far fa-eye-slash']]"></i><span class="sr-only">Toggle Visibility (currently [[visible ? 'visible' : 'invisible']])</span></button>
                        </div>
                        <div class="btn-group mr-2" role="group">
                            <button class="btn btn-sm btn-outline-secondary" on-click="removeItem" id="[[i]]" type="button"><i class="far fa-trash-alt"></i></button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class='col-md-12'>
                        <div id="comments_[[g]]">[[[comments]]]</div>
                    </div>
                    <ul id="group_[[id]]">
                        [[#items:i]]
                        [[#deleted != true]]
                        <li class="card" data-id="[[id]]">
                            <div class="card-header">
                                <span class="my-handle"><i class="fas fa-arrows-alt"></i></span>
                                <span contenteditable="true" value="[[title]]">[[title]]</span>
                                <div class="btn-toolbar float-right" role="toolbar" aria-label="Toolbar with button groups">
                                    <div class="btn-group mr-2" role="group">
                                        <div class="dropdown">
                                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton_[[g]]" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                Move to...
                                            </button>

                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                [[#~/course.grade_groups:g2]]
                                                <a class="dropdown-item" href="#/" on-click="['moveItemGroup',i,g,g2]">[[title]]</a>
                                                [[/~/course.grade_groups]]
                                            </div>

                                        </div>
                                    </div>
                                    <div class="btn-group mr-2" role="group">
                                        <button class="btn btn-sm [[options.stats ? 'btn-success' : 'btn-outline-secondary']]" on-click="@.toggle('course.grade_groups.'+g+'.items.'+i+'.options.stats')" id="[[i]]" type="button" data-toggle="tooltip" data-placement="top" title="Toggle Stats for Students"><i class="fas fa-chart-bar"></i><span class="sr-only">Toggle Visibility of Statistics for Students (currently [[options.stats ? 'visible' : 'invisible']])</span></button>
                                    </div>
                                    <div class="btn-group mr-2" role="group">
                                        <button class="btn btn-sm btn-outline-secondary" on-click="toggleVisible" id="[[i]]" type="button" data-toggle="tooltip" data-placement="top" title="Toggle Visibility"><i class="[[visible ? 'far fa-eye' : 'far fa-eye-slash']]"></i><span class="sr-only">Toggle Visibility (currently [[visible ? 'visible' : 'invisible']])</span></button>
                                    </div>
                                    <div class="btn-group mr-2" role="group">
                                        <button class="btn btn-sm btn-outline-secondary" on-click="removeItem" id="[[i]]" type="button"><i class="far fa-trash-alt"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class='col-md-12'>
                                    <div id="description_[[g]]_[[i]]">[[[comments]]]</div>
                                </div>
                                [[scores.length]] grades listed.
                                <div class="row [[~/show_stats ? '' : 'd-none']]">
                                    <div class="col-md-6">
                                        <div id="chart_[[g]]_[[i]]"  style="width:100%; height:200px;"></div>
                                    </div>
                                    <div class="col-md-6 my-auto">
                                        <ul>
                                            <li>N: [[~/stats[g][i].basics.num]]</li>
                                            <li>Mean: [[~/stats[g][i].basics.mean]]</li>
                                            <li>Std. Dev.: [[~/stats[g][i].basics.stdev]]</li>
                                            <li>Median: [[~/stats[g][i].basics.median]]</li>
                                            <li>[[~/stats[g][i].basics.excluded]] zeroes/non-numeric scores excluded from stats.</li>
                                        </ul>
                                    </div>
                                </div>
                                <!--[[#scores]]
                                [[~/students[user_id].firstname]] [[~/students[user_id].lastname]] [[earned]],
                                [[/scores]]-->
                            </div>
                        </li>
                        [[/deleted]]
                        [[/items]]
                    </ul>
                </div>
            </div>
                [[/deleted]]
                [[/course.grade_groups]]
                <br/>
                This block is a test of an improved layout.
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>Name</th>
                            [[#course.grade_groups]]
                            [[#items]]
                            <th>[[title]]</th>
                            [[/items]]
                            [[/course.grade_groups]]
                        </tr>
                        </thead>
                        <tbody>
                        [[#grade_table]]
                        <tr>
                            <td>[[firstname]]&nbsp[[lastname]]</td>
                            [[#grade_items]]
                            <td>[[earned]]</td>
                            [[/grade_items]]
                        </tr>
                        [[/grade_table]]
                        </tbody>
                    </table>
                </div>

        </div>
    </div>
</div>
