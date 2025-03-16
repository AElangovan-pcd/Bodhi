<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GradeItem extends Model
{
    protected $casts = ['options' => 'array'];

    public function group() {
        return $this->belongsTo('App\GradeGroup');
    }

    public function scores() {
        return $this->hasMany('App\GradeScore');
    }

    public function stats_scores() {
        return $this->hasMany('App\GradeScore')->select('earned','grade_item_id')->orderBy('earned');
    }

    // @Override
    public function delete()
    {
        // Delete all scores for this item.
        $this->scores()->delete();

        // Delete the group
        return parent::delete();
    }
}
