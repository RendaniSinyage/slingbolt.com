<?php

namespace App\Listeners;

use App\Events\StatusChangeProposal;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class StatusChangeProposalListener
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
     * @param  \App\Events\StatusChangeProposal  $event
     * @return void
     */
    public function handle(StatusChangeProposal $event)
    {
        //
    }
}
