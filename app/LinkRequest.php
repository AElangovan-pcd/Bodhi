<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LinkRequest extends Model
{
    protected $fillable = ['parent_id','child_id','status'];

    public function child_course() {
        return $this->hasOne('App\Course','id','child_id');
    }

    public function parent_course() {
        return $this->hasOne('App\Course','id','parent_id');
    }
}
