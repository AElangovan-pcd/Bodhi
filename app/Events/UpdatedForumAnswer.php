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

class UpdatedForumAnswer implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $course_id;
    public $forum_id;
    public $forum_answer_id;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($course_id, $forum_id, $forum_answer_id)
    {
        $this->course_id = $course_id;
        $this->forum_id = $forum_id;
        $this->forum_answer_id = $forum_answer_id;
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
