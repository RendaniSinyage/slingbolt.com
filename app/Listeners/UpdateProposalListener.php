<?php

namespace App\Listeners;

use App\Events\UpdateProposal as UpdateProposalEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateProposalListener
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
     * @param  \App\Events\UpdateProposal  $event
     * @return void
     */
    public function handle(UpdateProposalEvent $event)
    {
        //
    }
}
