<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            [[#questions:q]]
            <div class="card mt-2">
                <div class="card-header">[[name]]</div>
                <div class="card-body">
                    [[[description]]]
                    <div class="list-group">
                        [[#choices:c]]
                        <div class="list-group-item">
                            [[name]] <span class="float-right">[[count]]</span>
                        </div>
                        [[/choices]]
                        [[#type===2]]
                        Average Word Count: [[Math.round(wordCount)]]
                        [[/type]]
                    </div>
                </div>
            </div>
            [[/questions]]
        </div>
    </div>
</div>
