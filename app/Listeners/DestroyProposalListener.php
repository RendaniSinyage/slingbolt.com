<?php

namespace App\Listeners;

use App\Events\DestroyProposal;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DestroyProposalListener
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
     * @param  \App\Events\DestroyProposal  $event
     * @return void
     */
    public function handle(DestroyProposal $event)
    {
        //
    }
}
