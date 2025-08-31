<?php

namespace App\Events;

use App\Models\DebitNote;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class UpdateDebitNote
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $request;
    public $debitNote;

    public function __construct(DebitNote $debitNote, Request $request)
    {
        $this->request = $request;
        $this->debitNote = $debitNote;
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
