<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-warning" role="alert">
                    {{ session('error') }}
                </div>
            @endif
            <h3>Course Totals</h3>
            <button type="button" class="btn btn-info btn-sm mt-1 mb-1" on-click="csv" data-toggle="modal" data-target="#csv_modal">
                Export
            </button>
            <table class="table  table-hover">
                <thead>
                <tr>
                    <th on-click="['sort','first']">First Name</th>
                    <th on-click="['sort','last']">Last Name</th>
                    <th on-click="['sort','seat']">Seat</th>
                    [[#course.assignments:a]]
                    <th class="text-center" on-click="['sort',a]">[[name]] ([[total]])</th>
                    [[/course.assignments]]
                </tr>
                </thead>
                <tbody>
                [[#course.students:s]]
                <tr>
                    <td class="align-middle">
                        [[firstname]]
                    </td>
                    <td class="align-middle">
                        [[lastname]]
                    </td>
                    <td class="align-middle">
                        [[pivot.seat]]
                    </td>
                    [[#totals:t]]
                    <td>[[this]]</td>
                    [[/totals]]
                </tr>
                [[/course.students]]
                </tbody>
            </table>

        </div>
    </div>
</div>

<!-- Modal for csv -->
<div id="csv_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Export</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>You can copy and paste this into a spreadsheet:</p>
                <textarea class ="form-control" id="modal_csv"
                          value="[[csv_text]]">
                        </textarea>
                <br><p>Or click save to download the file.</p>
            </div>
            <div class="modal-footer">
                <button type="button" on-click="csv_download" class="btn btn-primary" id="save_csv">Save</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
