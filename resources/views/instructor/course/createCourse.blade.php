@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <h1>Create a Class</h1>

                <form action="createCourse" method="post">
                    @csrf
                    <label>Course Name: </label><input type="text" name="name" class="input-group"></input><br>
                    <label>Key:
                        <i class="far fa-question-circle" aria-hidden data-toggle="tooltip" data-placement="right" title="The key is something that students need to know in order to join the course."></i>
                        <span class="sr-only">The key is something that students need to know in order to join the course.</span>
                    </label><input type="text" name="key" class="input-group"></input><br>
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
