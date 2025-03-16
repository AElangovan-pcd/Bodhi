<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CourseFile extends Model
{
    public function course() {
        return $this->belongsTo('App\Course');
    }

    public function folder() {
        return $this->belongsTo('App\Folder');
    }
}
