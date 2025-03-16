<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ForumSubscription extends Model
{
    public function user(){
        return $this->belongsTo('App\User');
    }

    public function course()
    {
        return $this->belongsTo('App\Course');
    }
}
