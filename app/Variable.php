<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Variable extends Model
{
    protected $casts = ['choices' => 'array'];

    public function question() {
        return $this->belongsTo('App\Question');
    }

    public function answers() {
        return $this->hasMany('App\Answer');
    }

    public function answer() {
        return $this->hasOne('App\Answer')->latest();
    }

    public function getName() {
        return $this->name;
    }

    public function getValue() {
        return $this->value;
    }

    // Get the latest answer for this variable by a given user
    public function get_user_answer($user)
    {
        $answers = Answer::where("user_id", "=", $user->id)
            ->where("variable_id", "=", $this->id);

        $answer = $answers->orderBy('updated_at')->first();
        return $answer;
    }

    public function deactivate()
    {
        $this->active = false;
        $this->save;
    }
}
