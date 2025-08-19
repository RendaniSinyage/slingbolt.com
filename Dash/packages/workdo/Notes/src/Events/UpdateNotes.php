<?php

namespace Workdo\Notes\Events;

use Illuminate\Queue\SerializesModels;

class UpdateNotes
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $note;

    public function __construct($request, $note)
    {
        $this->request = $request;
        $this->note = $note;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
