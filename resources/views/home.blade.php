@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                <div class="card mb-2">
                    <div class="card-header">Tools</div>
                    <div class="card-body">
                        <a href="{{url('join')}}" role="button" class="btn btn-light btn-sm mb-1">
                            Join Course
                        </a>
                        <a href="{{url('changePassword')}}" role="button" class="btn btn-light btn-sm mb-1">
                            Change Password
                        </a>
                        <button type="button" class="btn btn-light btn-sm mb-1" data-toggle="modal" data-target="#nameModal">
                            Change Name
                        </button>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">Courses</div>
                    <div class="card-body">
                        <ul class="list-group flex-column">
                            @foreach ($courses as $course)
                                <a class="list-group-item d-flex justify-content-between align-items-center" href="{{url('course/'.$course->id.'/landing')}}">
                                    {{$course->name}}
                                </a>
                            @endforeach
                        </ul>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for changing name -->
    <div class="modal fade" id="nameModal" tabindex="-1" role="dialog" aria-labelledby="nameModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="nameModalLabel">Change Name</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{url('/changeName')}}" method="post">
                        @csrf
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">First</span>
                            </div>
                            <input type="text" name="new_firstname" value="{{$user->firstname}}" id="new_firstname">
                        </div>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">Last</span>
                            </div>
                            <input type="text" name="new_lastname" value="{{$user->lastname}}" id="new_lastname">
                        </div>
                        <button action="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
