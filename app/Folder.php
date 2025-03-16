<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    protected $casts = ['options' => 'array'];

    public function course() {
        return $this->belongsTo('App\Course');
    }

    public function course_files() {
        return $this->hasMany('App\CourseFile');
    }

    // @override
    public function delete() {
        foreach($this->course_files as $cf) {
            $cf->delete();
        }

        return parent::delete();
    }
}
