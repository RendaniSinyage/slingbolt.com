<?php

namespace App\Listeners;

use App\Events\UpdateProposal;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateProposal
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
    public function handle(UpdateProposal $event)
    {
        //
    }
}
