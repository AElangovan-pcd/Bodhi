<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class QuestionAnswered implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $course_id;
    public $user;
    public $assignment_id;
    public $question_id;
    public $result;
    public $answers;


    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($course_id, $user, $assignment_id, $question_id, $result, $answers)
    {
        $this->course_id = $course_id;
        $this->user = $user;
        $this->assignment_id = $assignment_id;
        $this->question_id = $question_id;
        $this->result = $result;
        $this->answers = $answers;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('course-instructor.'.$this->course_id);
    }
}
