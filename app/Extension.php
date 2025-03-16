<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Extension extends Model
{
    protected $fillable = ['assignment_id', 'user_id', 'expires_at', 'lock', 'lock_message'];

    protected $casts = ['expires_at' => 'datetime:Y-m-d H:i'];

    public function assignment() {
        return $this->belongsTo('App\Assignment');
    }

    public function user() {
        return $this->belongsTo('App\User');
    }
}
