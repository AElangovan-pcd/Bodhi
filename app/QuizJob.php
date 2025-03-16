<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class QuizJob extends Model
{
    protected $casts = [
        'options' => 'array',
        'question_list' => 'array',
        'allowed_start' => 'datetime:Y-m-d H:i',
        'allowed_end' => 'datetime:Y-m-d H:i',
        'actual_start' => 'datetime:Y-m-d H:i'
    ];

    protected $appends = ['loaded_time'];

    protected $fillable = ['assignment_id', 'user_id','allowed_start', 'allowed_end', 'allowed_minutes', 'status', 'question_list', 'review_state'];

    public function assignment() {
        return $this->belongsTo('App\Assignment');
    }

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function getLoadedTimeAttribute() {
        return $this->attributes['loaded_time'] = Carbon::now()->toDateTimeString();
    }

    public function updateTiming($job) {
        try {
            $this->allowed_minutes = $job->allowed_minutes;
            $this->allowed_start = Carbon::createFromFormat('Y-m-d H:i', $job->allowed_start);
            $this->allowed_end = Carbon::createFromFormat('Y-m-d H:i', $job->allowed_end);
            $this->save();
            return ["status" => 'success', 'quiz_job' => $this];
        }
        catch(\Exception $e) {
            return ['status' => 'fail', 'message' => $e->getMessage()];
        }
    }

    public function updateStatus($status) {
        try {
            $this->status = $status;
            $this->save();
            return ["status" => 'success', 'quiz_job' => $this];
        }
        catch(\Exception $e) {
            return ['status' => 'fail', 'message' => $e->getMessage()];
        }
    }

    public function updatePageStatus($page, $status) {
        try {
            $question_list = $this->question_list;
            $question_list[$page]['status'] = $status;
            $this->question_list = $question_list;
            $this->save();
            return ["status" => 'success', 'quiz_job' => $this];
        }
        catch(\Exception $e) {
            return ['status' => 'fail', 'message' => $e->getMessage()];
        }
    }

    public function complete_question_list() {
        $list = [];
        foreach($this->question_list as $index => $page) {
            foreach($page['ids'] as $id)
                $list[] = $id;
        }
        return $list;
    }

}
