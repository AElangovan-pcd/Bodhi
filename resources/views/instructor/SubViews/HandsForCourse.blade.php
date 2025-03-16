@if(isset($hands))
    @if(count($hands)>0)
        <!--<span style="color:#fff">{{$hands[0]->user->username.' | '.$hands[0]->get_seat()}}</span>-->
        <span class="d-none d-md-inline">{{$hands[0]->user->firstname.' | '.$hands[0]->get_seat()}}</span>
        {!! "<button class=\"btn btn-sm btn-link\" id=\"".$hands[0]->user->id."\" onclick=\"dismissHand(this)\" style=\"cursor: pointer;\">Dismiss</button>"!!}
        <span class="dropdown">
            @if(count($hands)<4)
                <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    {{ count($hands) }}
                </button>
            @else
                <button type="button" class="btn btn-danger btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    {{ count($hands) }}
                </button>
            @endif
            <div class="dropdown-menu dropdown-menu-right" role="menu">
            @foreach ($hands as $hand)
                <!--<li style="color:black">&nbsp{{$hand->user->username}} <div class="pull-right">{{$hand->get_seat()}}&nbsp</div></li>-->
                    <button class="dropdown-item type="button">&nbsp{{$hand->user->firstname.' '.$hand->user->lastname}} <span class="text-right">{{$hand->get_seat()}}&nbsp</span></button>
                @endforeach
            </div>
        </span>
    @else
        <span class="d-none d-md-inline">No students waiting</span>
        <button type="button" class="btn btn-success btn-sm d-inline">
            0
        </button>
    @endif
@endif
