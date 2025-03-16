<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GradeScore extends Model
{

    public function item() {
        return $this->belongsTo('App\GradeItem');
    }

    public function user() {
        return $this->belongsTo('App\User');
    }
}
