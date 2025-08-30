<?php

namespace App\Listeners;

use App\Events\OrderBugStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class OrderBugStatusListenerListener
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
     * @param  \App\Events\OrderBugStatus  $event
     * @return void
     */
    public function handle(OrderBugStatus $event)
    {
        //
    }
}
