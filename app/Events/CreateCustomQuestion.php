<?php

namespace App\Events;

use App\Models\CustomQuestion;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class CreateCustomQuestion
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $request;
    public $customQuestion;

    public function __construct(CustomQuestion $customQuestion, Request $request)
    {
        $this->request = $request;
        $this->customQuestion = $customQuestion;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
