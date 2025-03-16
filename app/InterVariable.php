<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InterVariable extends Model
{
    //protected $table = "intermediate_variables";

    public function question()
    {
        return $this->belongsTo('App\Question');
    }

    public function deactivate()
    {
        $this->active = false;
        $this->save;
    }
}
