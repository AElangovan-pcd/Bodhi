@extends('layouts.app')

@section('JS')
    <script src="{{ asset('js/ractive.min.js') }}"></script>
@endsection

@section('content')

    <div id="target"></div>

    <script id="template" type="text/ractive">
        @include('admin.manageCoursesPage')
    </script>

    <script>
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

        ractive.on('filter', function(event) {
           ractive.set('filter',event.node.id);
           ractive.update();
        });

        //Sort function
        ractive.sort = ractive.sort = function ( column ) {
            var array = Object.values(this.get("courses"));

            //If the table is already sorted by this column, reverse it.
            if(column == ractive.get("sortColumn")) {
                array.reverse();
                this.set("courses", array);
                return;
            }

            array.sort(function(a, b) {
                if(column == 'owner') {
                    a = a.owner.lastname.toLowerCase();
                    b = b.owner.lastname.toLowerCase();
                }
                else if(column == 'active') {
                    a = a[column];
                    b = b[column];
                }
                else {
                    a = a[column].toLowerCase();
                    b = b[column].toLowerCase();
                }
                return a < b ? -1 : 1;
            });
            this.set("courses",array);
            this.set("sortColumn", column);
        };

    </script>
@endsection
