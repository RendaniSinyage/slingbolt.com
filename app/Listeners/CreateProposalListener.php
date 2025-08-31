<?php

namespace App\Listeners;

use App\Events\CreateProposal;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateProposalListener
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
     * @param  \App\Events\CreateProposal  $event
     * @return void
     */
    public function handle(CreateProposal $event)
    {
        //
    }
}
