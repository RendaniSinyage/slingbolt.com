<?php

namespace App\Events;

use App\Models\Holiday;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class CreateHoliday
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $request;
    public $holiday;

    public function __construct(Holiday $holiday, Request $request)
    {
        $this->request = $request;
        $this->holiday = $holiday;
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
