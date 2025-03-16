@extends('layouts.app')

@section('hands')
    <div id="hands" class="navbar-text"></div>
    <div id="polls" class="navbar-text">
        <a class="btn btn-sm btn-outline-dark" data-toggle="tooltip" data-placement="bottom" title="No active polls" href="{{url( '/instructor/course/'.$course->id.'/polls/landing' )}}">
            <i class="fas fa-vote-yea"></i>
        </a>
    </div>
    <div id="random" class="navbar-text">
        <div class="btn btn-sm btn-outline-dark" onclick="random_student()"><i class="fas fa-dice"></i></div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="randomModal" tabindex="-1" role="dialog" aria-labelledby="randomModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="randomModalLabel">Random Student</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <span id="random_text"></span>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('custom_JS')
    @include('layouts.instructor_js')
@stop

