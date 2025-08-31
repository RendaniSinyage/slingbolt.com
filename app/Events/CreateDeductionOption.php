<?php

namespace App\Events;

use App\Models\DeductionOption;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class CreateDeductionOption
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $request;
    public $deductionOption;

    public function __construct(DeductionOption $deductionOption, Request $request)
    {
        $this->request = $request;
        $this->deductionOption = $deductionOption;
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
