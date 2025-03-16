@extends('layouts.app')

@section('hands')
    <a href="{{url( '/course/'.$course->id.'/polls/participate' )}}"><div id="polls" class="navbar-text"><i class="fas fa-vote-yea" data-toggle="tooltip" data-placement="bottom" title="No active polls"></i></div></a>
    <div id="hands" class="navbar-text"><span id="hand_button"></span><span id="output" class="btn btn-sm btn-primary disabled" style="display:none"></span></div>
@stop

@section('custom_JS')
    @include('layouts.student_js')
@stop
