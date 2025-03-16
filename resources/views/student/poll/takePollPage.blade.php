<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif
            <h2>[[poll.name]]</h2>
            <hr>
            <h4>[[&poll.question]]</h4>
            [[#if poll.type == 0]]
            <form class="form-group">
                [[#poll.choices:c]]
                <label class="poll-choice" id='c_[[c]]'>
                    [[#if c == choice_index]]
                    <input type="radio" name="@c" id="[[c]]" on-click="choose" disabled="[[answered]]" checked="true" />
                    [[else]]
                    <input type="radio" name="@c" id="[[c]]" on-click="choose" disabled="[[answered]]" />
                    [[/if]]
                    [[[this]]]
                </label>
                [[/poll.choices]]
            </form>
            [[elseif poll.type ===1]]
            <form class="form-group" on-submit="submit">
                <input type="text" class="form-control" id="shortAnswerResponse" placeholder="Enter your response here." value="[[SAval]]" disabled="[[answered]]">
            </form>
            [[elseif poll.type === 2]]

            <button class="btn btn-primary" id="clear" style="float:left;">Clear</button>
            <div id="spacer" style="background-color:white;width:10px; height:34px; float:left;"> </div>
            <div id="black" style="background-color:#000;width:30px; height:34px; float:left;"> </div>
            <div id="red" style="background-color:#E74C3C; width:30px; height:34px; float:left;"> </div>
            <div id="orange" style="background-color:#F3B212; width:30px; height:34px; float:left;"> </div>
            <div id="green" style="background-color:#2ECC71; width:30px; height:34px; float:left;"> </div>
            <div id="blue" style="background-color:#3498DB; width:30px; height:34px; float:left;"> </div>
            <div id="purple" style="background-color:#8E44AD; width:30px; height:34px; float:left;"> </div>

            <div style="clear:left"></div>
            <div id="canvasArea">

                <img id="bgImage" src='data:image/gif;base64,R0lGODlhAQABAAAAACwAAAAAAQABAAA='>

                <canvas id="myCanvas">
                    Sorry, your browser does not support HTML5 canvas technology.
                </canvas>
            </div>
            [[elseif poll.type == undefined]]
                There is no active poll.
            [[else]]
            <p>Problem with poll type.</p>
            [[/if]]
            <hr>
            [[#if poll.type != undefined && !answered]]
            <button class="btn btn-primary" on-click="submit" id="submit">Submit</button>
            [[/if]]
        </div>
    </div>
</div>
