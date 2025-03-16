<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CachedAnswer extends Model
{
    protected $fillable = ['user_id', 'assignment_id','values'];

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function assignment() {
        return $this->belongsTo('App\Assignment');
    }
}
