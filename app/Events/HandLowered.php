<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\User;
use App\Hand;

class HandLowered implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $course;
    protected $user_id;
    protected $hands;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user_id, $course, $hands)
    {
        $this->course = $course;
        $this->hands = $hands;
        $this->user_id = $user_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $channels = array(
            new PrivateChannel('course-instructor.'.$this->course->id),
            new PrivateChannel('App.User.'.$this->user_id.'.Course.'.$this->course->id),
        );
        foreach ($this->hands as $hand)
            array_push($channels, new PrivateChannel('App.User.'.$hand->user_id.'.Course.'.$this->course->id));
        return $channels;
    }
}
