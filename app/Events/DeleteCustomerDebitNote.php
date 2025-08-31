<?php

namespace App\Events;

use App\Models\CustomerDebitNotes;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeleteCustomerDebitNote
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $customerDebitNote;

    public function __construct(CustomerDebitNotes $customerDebitNote)
    {
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
