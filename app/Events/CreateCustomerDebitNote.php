<?php

namespace App\Events;

use App\Models\CustomerDebitNotes;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class CreateCustomerDebitNote
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $request;
    public $customerDebitNote;

    public function __construct(CustomerDebitNotes $customerDebitNote, Request $request)
    {
        $this->request = $request;
        $this->customerDebitNote = $customerDebitNote;
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
