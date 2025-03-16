<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Forum extends Model
{
    public function user() {
        return $this->belongsTo('App\User');
    }

    public function course()
    {
        return $this->belongsTo('App\Course');
    }

    public function forum_answers() {
        return $this->hasMany('App\ForumAnswer');
    }

    public function forum_views() {
        return $this->hasMany('App\ForumView');
    }

    public function latest_view() {
        return $this->hasOne('App\ForumView')->latest();
    }

    public function forum_subscriptions() {
        return $this->hasMany('App\ForumSubscription');
    }

    public function topic_subscriptions() {
        return $this->hasMany('App\ForumTopicSubscription');
    }

    public function viewers() {
        return $this->hasMany('App\ForumView');
    }

    public function voters() {
        return explode('|', $this->votes);
    }

    public function tags() {
        return explode('|', $this->tags);
    }

    // @override
    public function delete()
    {
        // Delete everything underneath
        foreach($this->forum_answers as $f)
        {
            $f->delete();
        }

        foreach($this->forum_views as $f)
        {
            $f->delete();
        }

        foreach($this->topic_subscriptions as $f)
        {
            $f->delete();
        }

        //Delete the assignment
        return parent::delete();
    }
}
