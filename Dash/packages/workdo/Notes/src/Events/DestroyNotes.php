<?php

namespace Workdo\Notes\Events;

use Illuminate\Queue\SerializesModels;

class DestroyNotes
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $note;

    public function __construct($note)
    {
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
