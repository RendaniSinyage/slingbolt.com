<?php

namespace App\Listeners;

use App\Events\SentProposal;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SentProposal
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\SentProposal  $event
     * @return void
     */
    public function handle(SentProposal $event)
    {
        //
    }
}
