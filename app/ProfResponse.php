<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProfResponse extends Model
{
    protected $fillable=['question_id','response','order'];

    public function question() {
        return $this->belongsTo('App\Question');
    }
}
