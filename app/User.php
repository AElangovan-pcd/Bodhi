<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Jobs\QueuedVerifyEmail;
use App\Jobs\QueuedResetPassword;
use App\Score;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;

    const DECIMAL_VARIABLE = 0;
    const COLUMN_VARIABLE = 1;
    const STRING_VARIABLE = 2;

    const STANDARD_QUESTION = 1;
    const SHORT_ANSWER_QUESTION = 2;
    const SIMPLE_QUESTION = 3;
    const UNANSWERED_QUESTION = 4;
    const SIMPLE_TEXT_QUESTION = 5;
    const MOLECULE_QUESTION = 6;
    const MULTIPLE_CHOICE_QUESTION = 7;

    const PERCENT = 0;
    const RANGE = 1;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname', 'lastname', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    // Model Relationships

    public function courses() {
        return $this->belongsToMany('App\Course')
            ->withPivot('seat','multiplier');
    }

    public function hands(){
        return $this->hasMany('App\Hand');
    }

    public function seats(){
        return $this->hasMany('App\Seat');
    }

    public function seat_for_course($cid){
        return Seat::where('course_id', $cid)->where('name',$this->courses->firstWhere('id',$cid)->pivot->seat)->first();
    }

    public function classrooms(){
        return $this->hasMany('App\Classroom');
    }

    public function fullNameLastFirst() {
        return $this->lastname . ", " . $this->firstname;
    }

    public function owned_courses() {
        return Course::where("owner", $this->id)->get()->sortBy(function($c) {
            return $c->name;
        });
    }

    public function assignments()
    {
        return $this->hasMany('App\Assignment', "creator_id");
    }

    public function review_submissions() {
        return $this->hasMany('App\ReviewSubmission');
    }

    public function review_revision_submissions() {
        return $this->hasMany('App\ReviewRevisionSubmission');
    }

    public function review_jobs() {
        return $this->hasMany('App\ReviewJob');
    }

    public function review_feedbacks() {
        return $this->hasMany('App\ReviewFeedback');
    }

    public function scores() {
        return $this->hasMany('App\Score');
    }

    public function answers() {
        return $this->hasMany('App\Answer');
    }

    public function cachedAnswers() {
        return $this->hasMany('App\CachedAnswer');
    }

    public function cached_answer() {
        return $this->hasOne('App\CachedAnswer');
    }

    public function response()
    {
        return $this->hasMany('App\ProfResponse');
    }

    public function info_quiz_answers() {
        return $this->hasMany('App\InfoQuizAnswer');
    }

    public function forums() {
        return $this->hasMany('App\Forum');
    }

    public function forum_answers() {
        return $this->hasMany('App\ForumAnswer');
    }

    public function forum_endorsed_answers() {
        return $this->hasMany('App\ForumAnswer')->where('endorsed',1);
    }

    public function forum_helpful_answers() {
        return $this->hasMany('App\ForumAnswer')->has('votes');
    }

    public function forum_helpful_votes() {
        return $this->hasMany('App\ForumAnswer')->select(['id','course_id','user_id'])->withCount('forum_votes');
    }

    public function helpful() {
        $this->forum_helpful_votes()->sum('forum_votes_count');
    }

    public function forum_views() {
        return $this->hasMany('App\ForumView');
    }

    public function forum_votes() {
        return $this->hasMany('App\ForumResponseVote');
    }

    /**
     * Send the email verification email.
     * Overriding the default behavior to use a queue.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        QueuedVerifyEmail::dispatch($this);
    }

    /**
     * Send the password reset notification.
     * Overriding the default behavior to use a queue.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        QueuedResetPassword::dispatch($this, $token);
    }
}
