<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header">Question</div>
                        <div class="card-body">
                            <h3>[[poll.name]]</h3>
                            [[[poll.question]]]
                            [[#poll.choices:c]]
                            <label class="poll-choice" id='[[c]]' style="background-color:[[poll.colors[c]]];">
                                [[this]]
                            </label>
                            [[/poll.choices]]
                        </div>
                        <div class="card-footer">
                            [[#!poll.complete]]
                            <div class="btn btn-info btn-sm" on-click="complete_poll" id="[[id]]">Close Poll</div>
                            [[else]]
                            <div class="btn btn-success btn-sm mb-1" on-click="allow_answers" id="[[id]]">Re-Open Poll</div>
                            <div class="btn btn-outline-dark btn-sm" on-click="duplicate" id="[[id]]">Duplicate and Launch</div>
                            [[openMsg]]
                            [[/poll.complete]]
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card h-100">
                        <div class="card-header">Results</div>
                        <div class="card-body">
                            [[#if poll_type == 0]]
                            <div id = "canvasWrapper" style="height:300 px">
                                <canvas id="myChart" width="300" height="300" class="mt-3"></canvas>
                            </div>
                            [[elseif poll_type == 1]]
                            <div id="chart"></div>
                            [[elseif poll_type == 2]]
                            <div class="container" id="overlay" width=[[overlaySize]]; height=[[overlaySize]];>
                                <img src="[[poll.image]]" width=[[overlaySize]] height=[[overlaySize]] id="bg" style="position: absolute; top=0; left=0; z-index:0; " >
                                [[#each students: num]]
                                [[#if (answer !="" && ../../mode == "overlay") || (answer !="" && ../../mode == "individual" && ../../selected == id)]]
                                <img src="[[answer]]" width=[[../../overlaySize]] height=[[../../overlaySize]] style="position: absolute; z-index:[[num+1]]; " id="response"/>
                                [[/if]]
                                [[/each students]]
                                <img src="[[poll.image]]" width=[[overlaySize]] height=[[overlaySize]] id="bg">
                            </div>
                            [[/if]]
                        </div>
                        <div class="card-footer">
                            Responses: [[answerCount]]&nbsp;
                            [[#if poll_type == 0]]
                            <button class="btn btn-outline-dark btn-sm" on-click="toggle_results">
                                [[results ? "Hide All Results" : "Show All Results"]]
                            </button>
                            <button class="btn btn-outline-dark btn-sm" on-click="toggle_classroom">
                                [[classroom ? "Hide Classroom" : "Show Classroom"]]
                            </button>
                            [[elseif poll_type ==1]]
                            <button class="btn btn-outline-dark btn-sm" on-click="redraw">[[newResp ? "Redraw (new responses)" : "Redraw"]]</button>
                            <button class="btn [[autoRedraw ? "btn-info" : "btn-outline-dark"]] btn-sm" on-click="toggleAutoRedraw">Auto Redraw</button>
                            <button class="btn [[ignoreCommon ? "btn-info" : "btn-outline-dark"]] btn-sm" on-click="toggleIgnoreCommon">Ignore Common Words</button>
                            <button class="btn btn-outline-dark btn-sm" on-click="toggle_users">[[results ? "Hide Student Info" : "Show Student Info"]]</button>
                            [[elseif poll_type ==2]]
                            <button class="btn [[mode == "overlay" ? "btn-info" : "btn-outline-dark"]] btn-sm" on-click="overlaymode" id="overlaymode">Show Overlay</button>
                            <button class="btn [[mode == "individual" ? "btn-info" : "btn-outline-dark"]] btn-sm" on-click="individualmode" id="individualmode">Select Individual</button>
                            <button class="btn btn-outline-dark btn-sm" on-click="shrink" id="shrink">Shrink</button>
                            <button class="btn btn-outline-dark btn-sm" on-click="expand" id="expand">Expand</button>
                            [[/if]]
                        </div>
                    </div>
                </div>
            </div>

            [[#if poll_type != 2]]
            <button id="tsv_button" class="btn btn-outline-dark" on-click="tsv" style="display: [[class]]">
                TSV <span class="glyphicon glyphicon-copy"></span>
            </button>
            [[/if]]

            [[#if poll_type == 2]]

            <div class="btn btn-outline-dark" on-click="shrink-ind" id="shrink-ind">Shrink</div>
            <div class="btn btn-outline-dark" on-click="expand-ind" id="expand-ind">Expand</div>
            <div class="btn btn-outline-dark" on-click="toggle_users">
                [[results ? "Hide Student Info" : "Show Student Info"]]
            </div>
            Sort by:
            <div class="btn btn-outline-dark" on-click="sort" id="firstname">First Name</div>
            <div class="btn btn-outline-dark" on-click="sort" id="lastname">Last Name</div>
            <div class="btn btn-outline-dark" on-click="sort" id="seat">Seat</div>



            [[/if]]


            <table id="results_table" class="table table-striped fade-in fade-out" style="display: [[class]]">
                <thead>
                [[#if poll_type !=2]]
                [[#if results]]
                <th on-click="sort" id="firstname">First Name</th>
                <th on-click="sort" id="lastname">Last Name</th>
                <th on-click="sort" id="email">Email</th>
                <th on-click="sort" id="seat">Seat</th>
                [[/if]]
                [[/if]]
                <th on-click="sort" id="answer">Answer</th>
                </thead>
                <tbody>
                [[#if poll_type==2]]
                <tr>
                    [[#each students]]
                    <td style="display:inline-block">
                        [[#if results]]
                        [[firstname]]
                        [[lastname]]
                        [[pivot.seat]]
                        [[/if]]
                        <div on-click="select" id="[[id]]">
                            <img src="[[../../poll.image]]" width=[[../../individualSize]] height=[[../../individualSize]] id="bg" style="position: absolute; top=0; left=0;">
                            [[#if answer !=""]]
                            <img src="[[answer]]" width=[[../../individualSize]] height=[[../../individualSize]] style="position: relative;" id="response"/>
                            [[else]]
                            <img src="data:image/png;base64,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" width=[[individualSize]] height=[[individualSize]] style="position: relative;" id="response"/>
                            [[/if]]
                        </div>
                    </td>
                    [[/each students]]
                </tr>
                [[else]]
                [[#each students]]
                <tr>
                    [[#if results]]
                    <td>[[firstname]]</td>
                    <td>[[lastname]]</td>
                    <td>[[email]]</td>
                    <td>[[pivot.seat]]</td>
                    [[/if]]
                    <td>[[answer]]</td>

                </tr>
                [[/each students]]
                [[/if]]
                </tbody>
            </table>
            [[#if poll_type == 0]]
            <div class="row mb-3" id="classroom">
                <div class="col-md-4">
                    <div class="card h-100 mb-2" >
                        <div class="card-header">Layout Selection</div>
                        <div class="card-body">
                            <button class="btn btn-sm btn-outline-dark mt-1" id="-1" on-click="focus">All</button>
                            <br/>
                            [[#poll.choices:c]]
                            <button class="btn btn-sm mr-1 mt-1" id='[[c]]' style="background-color:[[poll.colors[c]]];" on-click="focus">
                                [[this]]
                            </button>
                            [[/poll.choices]]
                        </div>
                        <div class="card-footer">
                            Select a choice to highlight in the classroom layout map.
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card h-100" id="classroom">
                        <div class="card-header">
                            Classroom Layout
                        </div>
                        <div class="card-body">
                            <canvas id="classCanvas"></canvas>
                        </div>
                        [[#noSeats.length > 0]]
                        <div class="card-footer">
                            No seat location for:
                            [[#noSeats:ns]]
                            [[this.firstname]] [[this.lastname]] ([[this.pivot.seat]])[[ns < noSeats.length-1 ? ", " : ""]]
                            [[/noSeats]]
                        </div>
                        [[/noSeats]]
                    </div>
                </div>
            </div>

            [[/if]]
        </div>
    </div>
</div>
