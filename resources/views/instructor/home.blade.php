@extends('layouts.app')

@section('JS')
    <script src="{{ asset('js/ractive.min.js') }}"></script>
@endsection

@section('content')

    <div id="target"></div>

    <script id="template" type="text/ractive">
        @include('instructor.homePage')
    </script>

    <script>
        var data = {!! $data !!};
        console.log(data);

        var ractive = new Ractive({
            target: '#target',
            template: '#template',
            data: data,
            delimiters: [ '[[', ']]' ],
            computed: {
                archivedCount: function() {
                    let inactive = this.get("inactive");
                    cnt = 0;
                    Object.entries(inactive).forEach(
                        ([key, value]) => cnt+=value.archived
                    );
                    return cnt;
                }
            }
        });

        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        });

        /*var n = new Noty({
            theme: 'bootstrap-v4',
            type: 'information',
            layout:'centerRight',
            text: '<strong><i>Did you know?</i></strong><br>You can now select pre-made images for your classroom and upload pre-made seat layouts.  There are seat layout files in the General Chemistry Resources LabPal Dropbox folder.<br><i>Click to Dismiss</i>',
        }).show();*/

    </script>
@endsection
