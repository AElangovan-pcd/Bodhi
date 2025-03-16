<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PollAnswer extends Model
{
    public function user(){
        return $this->belongsTo('App\User');
    }

    public function poll()
    {
        return $this->belongsTo('App\Poll');
    }
}
