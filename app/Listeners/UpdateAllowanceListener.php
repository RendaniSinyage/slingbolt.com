<?php

namespace App\Listeners;

use App\Events\UpdateAllowance;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateAllowanceListenerListener
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
     * @param  \App\Events\UpdateAllowance  $event
     * @return void
     */
    public function handle(UpdateAllowance $event)
    {
        //
    }
}
