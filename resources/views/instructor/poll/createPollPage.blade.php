<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <h2>Create a Poll for [[course.name]]</h2>
            <hr>
            <label>Name:</label>
            <input type="text" class="form-control" value="[[poll_name]]">
            <label>Question:</label>
            <!--<textarea class="form-control" value="[[poll_question]]"></textarea>-->
            <div class="tex2jax_ignore">
                <div id="editor">
                    [[[poll_question]]]
                </div>
            </div>
            <br>
            <div class="btn btn-primary changeType" on-click="changeType" id="type">Change poll type</div>
            [[#if poll_type == 0]]
            Multiple choice
            [[elseif poll_type == 1]]
            Short Answer
            [[elseif poll_type == 2]]
            Drawing
            [[else]]
            There is an error with the poll types.
            [[/if]]
            <hr>

            <div class="row">
                [[#if poll_type == 0]]
                <div class="col-sm-3 border-right" id="adding">
                    <div class="input-group">
                        <input type="text" id="mainAdd" class="form-control" placeholder="Add an alternative, press Enter" value="[[new_choice]]" on-keypress="addChoice">
                        <span class="input-group-addon" on-click="addChoice"><span class="glyphicon glyphicon-plus"></span></span>
                    </div>
                    <h3>Quick Add</h3>
                    <div class="btn btn-primary mb-1" style="width:100%" on-click="quickAdd" id="2">2 Alternatives</div>
                    <div class="btn btn-primary mb-1" style="width:100%" on-click="quickAdd" id="3">3 Alternatives</div>
                    <div class="btn btn-primary mb-1" style="width:100%" on-click="quickAdd" id="4">4 Alternatives</div>
                </div>
                <div class="top-space"></div>
                <div class="col-sm-9" id="alternatives">
                    [[#choices:c]]
                    <div class="input-group">
                        <input type="text" class="form-control" id="choice_[[c]]" value="[[text]]" on-keypress="addChoice">
                        <div class="input-group-append">
                            <span class="input-group-text" id="[[c]]" name="[[text]]" on-click="removeChoice"><i class="far fa-trash-alt" aria-hidden></i><span class="sr-only">Remove</span></span>
                        </div>
                    </div>
                    [[/choices]]
                </div>
                [[elseif poll_type == 2]]
                <div class="col-sm-3 border-right" id="drawOptions">
                    <h3>Background</h3>
                    <div class="btn btn-primary mb-1" style="width:100%" on-click="drawOptions" id="blank">Blank Canvas</div>
                    <div class="btn btn-primary mb-1" style="width:100%" on-click="drawOptions" id="axes">Axes</div>
                    <div class="btn btn-primary mb-1" style="width:100%" on-click="customBG" id="custom">Custom Image</div>
                </div>


                <div class="top-space"></div>
                <div class="col-sm-6" id="alternatives">
                    [[drawMessage]]
                    [[#if customFlag ==1]]
                    <div class="input-group">
                        <input id="upload" type='file'>
                    </div>
                    [[/if]]
                </div>
                <div class="col-sm-3">
                    [[#if customFlag2 == 1]]

                    [[/if]]
                    <img id="bgImage" src='[[background]]' width=200 height=200 style="border:1px solid black">
                </div>


                [[/if]]


            </div>
            <div class="row">
                <div class="col-12 text-center">
                    [[#ready]]
                    <div class="btn btn-info" id="save"
                         tabindex="0" on-click="save" on-keypress="save">[[saving]]</div>
                    [[/ready]]
                </div>
            </div>
            [[#saving=="Saved"]]
            <div class="row mt-2">
                <div class="col-12 text-center">
                    <a href="{{url('instructor/course/'.$course->id.'/polls/landing')}}" class="btn btn-outline-dark">Poll Landing</a>
                    <a href="{{url('instructor/course/'.$course->id.'/polls/create')}}" class="btn btn-outline-dark">Create Another</a>
                    <btn class="btn btn-outline-success" on-click="activate" id="activate">Activate Poll</btn>
                    [[activateMsg]]
                </div>
            </div>
            [[/saving]]
        </div>
    </div>
</div>
