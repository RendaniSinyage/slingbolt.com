<?php

namespace App\Listeners;

use App\Events\ResentProposal;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ResentProposal
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
     * @param  \App\Events\ResentProposal  $event
     * @return void
     */
    public function handle(ResentProposal $event)
    {
        //
    }
}
