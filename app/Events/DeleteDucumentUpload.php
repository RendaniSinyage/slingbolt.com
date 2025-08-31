<?php

namespace App\Events;

use App\Models\DucumentUpload;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeleteDucumentUpload
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ducumentUpload;

    public function __construct(DucumentUpload $ducumentUpload)
    {
        $this->ducumentUpload = $ducumentUpload;
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
