<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <h3>Create a Discussion Topic for [[course.name]]</h3>
            <hr>
            <h4>Title:</h4>
            <input type="text" class="form-control" id="title" value="[[[forum_title]]]">
            <h4>Question:</h4>
            <!--<textarea class="form-control" value="[[forum_question]]"></textarea>-->
            <div class="tex2jax_ignore">
                <div id="editor">
                    [[[forum_question]]]
                </div>
            </div>
            <button class="btn btn-info mt-2" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                Help with equations
            </button>
            @include('forum.MathHelp')
            <h4 class="mt-2">Options:</h4>
            <div class="form-check-inline ml-2 mb-2">
                <input class="form-check-input" type="checkbox" value=[[anonymous]] on-click="check" [[#if anonymous]]checked[[/if]]>
                <label class="form-check-label">
                    Post anonymously. <a href="#/" class="text-decoration-none" data-toggle='tooltip' data-placement='right' title='Your instructor will know your identity, but you will be anonymous to your classmates.  Uncheck to show your name to your classmates.'><i class="fas fa-question-circle" aria-hidden ></i></a><span class="sr-only">Your instructor will know your identity, but you will be anonymous to your classmates.  Uncheck to show your name to your classmates.</span>
                </label>
            </div>
            <h4>Tags:</h4>
            <p>Add optional tags</p>
            <input type="text" class="form-control" id="tags" value="[[[tags]]]">
            <br>
            <button class="btn btn-success" id="save" on-click="save" on-keypress="save">[[saving]]</button>
        </div>
    </div>
</div>
