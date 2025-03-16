<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ForumTopicSubscription extends Model
{
    public function user(){
        return $this->belongsTo('App\User');
    }

    public function forum()
    {
        return $this->belongsTo('App\Forum');
    }
}
