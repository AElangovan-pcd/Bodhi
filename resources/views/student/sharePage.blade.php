<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <h2> [[assignment_name]]</h2><br>

            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link" href="view">Assignment</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="#">Shared Answers</a>
                </li>
            </ul>

            <span data-toggle="modal" data-target="#csv_modal">
                <button type="button" class="btn btn-light float-right" id="copy_table_button"
                        on-click="csv" data-toggle="tooltip" data-placement="top" title="Download Results">
                    <i class="fas fa-file-download" aria-hidden></i>
                    <span class="sr-only">Download Results</span>
                </button>
            </span>

            <table class="table table-striped">
                <thead>
                <tr>
                    [[#variables:v]]
                    <td>
                        <label on-click="sort" id="[[v]]">
                            [[#if this.type == "1"]]
                            <strong>[[name]]</strong>
                            [[else]]
                            <strong>[[title]]</strong>
                            [[/if]]
                            <p class="help-block">[[descript]]</p>
                        </label>
                    </td>
                    [[/variables]]
                </tr>
                </thead>

                <tbody>
                [[#rows]]
                <tr>
                    [[#this:t]]
                    <td style="max-width:100px; word-wrap:break-word;">
                        [[this]]
                    </td>
                    [[/.]]
                </tr>
                [[/rows]]
                </tbody>
            </table>

            <div id="csv_modal" class="modal fade" role="dialog">
                <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Share table in TSV (tab-separated)</h4>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <p>You can copy and paste this into Excel:</p>
                            <textarea class ="form-control" id="modal_csv" value="[[csv]]"></textarea>
                            <br><p>Or click save to download the file.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" on-click="csv_download">Save</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal" id="copy_to_clipboard">Close</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
