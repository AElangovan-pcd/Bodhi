<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GradeGroup extends Model
{

    public function course() {
        return $this->belongsTo('App\Course');
    }

    public function items() {
        return $this->hasMany('App\GradeItem')->orderBy('order');
    }

    // @Override
    public function delete()
    {
        // Delete all grade items within the group.
        $this->items()->delete();

        // Delete the group
        return parent::delete();
    }
}
