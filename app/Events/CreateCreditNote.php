<?php

namespace App\Events;

use App\Models\CreditNote;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class CreateCreditNote
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $request;

    public $creditNote;

    public function __construct(CreditNote $creditNote, Request $request)
    {
        $this->request = $request;
        $this->creditNote = $creditNote;
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
