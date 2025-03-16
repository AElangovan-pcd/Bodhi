@extends('layouts.app')

@section('JS')
    <script src="{{ asset('js/ractive.min.js') }}"></script>
@endsection

@section('content')

    <div id="target"></div>

    <script id="template" type="text/ractive">
        @include('instructor.manageStudentsPage')
    </script>

    <script>
        let url = "manageStudents/select";

        var data = {!! $data !!};
        console.log(data);

        var ractive = new Ractive({
            target: '#target',
            template: '#template',
            data: data,
            delimiters: [ '[[', ']]' ],
        });

        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        });

        ractive.on("section", function(event) {
            var section = event.get();
            var rows = ractive.get("rows");

            ractive.set("loading", true);
            $.post(url, {
                _token: "{{ csrf_token() }}",
                selection: event.get()
            }, function(data) {
                console.log(data);
                ractive.set("loading", false);
                ractive.set("students", JSON.parse(data));
            });
        });

    </script>
@endsection
