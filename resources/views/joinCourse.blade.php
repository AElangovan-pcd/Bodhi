@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                @if (session('error'))
                    <div class="alert alert-warning" role="alert">
                        {{ session('error') }}
                    </div>
                @endif
                <h1>Join Class</h1>
                <h2>{{$course->name}}</h2>
                <form action="{{$course->id}}" method="post" autocomplete="off">
                    @csrf
                    <label>Key:
                        <i class="far fa-question-circle" aria-hidden data-toggle="tooltip" data-placement="right" title="Ask your instructor for the course key."></i>
                        <span class="sr-only">Ask your instructor for the course key.</span>
                    </label>
                    <input type="text" name="key" class="input-group"></input><br>
                    <label>Seat:
                        <i class="far fa-question-circle" aria-hidden data-toggle="tooltip" data-placement="right" title="Ask your instructor what you should list for the seat."></i>
                        <span class="sr-only">Ask your instructor what you should list for the seat.</span>
                    </label><input type="text" name="seat" class="input-group"></input><br>
                    <input type="Submit" class="btn btn-primary" value="Submit"></input>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        });
    </script>
@stop
