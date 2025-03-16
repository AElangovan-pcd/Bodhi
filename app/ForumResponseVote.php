<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ForumResponseVote extends Model
{
    //use \Znck\Eloquent\Traits\BelongsToThrough;

    public function user(){
        return $this->belongsTo('App\User');
    }

    public function answer()
    {
        return $this->belongsTo('App\ForumAnswer','response_id');
    }

    /*public function course() {
        return $this->belongsToThrough(
            'App\Course',
                ['App\Forum','App\ForumAnswer'],
                null,
                    '',
                    ['App\ForumAnswer' => 'response_id']

            );
    }*/

}
