<?php

namespace App\Listeners;

use App\Events\DuplicateProposal;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DuplicateProposalListener
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
     * @param  \App\Events\DuplicateProposal  $event
     * @return void
     */
    public function handle(DuplicateProposal $event)
    {
        //
    }
}
