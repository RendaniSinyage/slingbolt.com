<?php

namespace App\Events;

use App\Models\CustomerCreditNotes;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeleteCustomerCreditNote
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $customerCreditNote;

    public function __construct(CustomerCreditNotes $customerCreditNote)
    {
        $this->customerCreditNote = $customerCreditNote;
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
