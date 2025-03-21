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

class NewComment implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $course_id;
    public $assignment_id, $question_id, $id, $contents;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($course_id, $assignment_id, $question_id, $id, $contents)
    {
        $this->course_id = $course_id;
        $this->assignment_id = $assignment_id;
        $this->question_id = $question_id;
        $this->contents = $contents;
        $this->id = $id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('course-all.'.$this->course_id);
    }
}
