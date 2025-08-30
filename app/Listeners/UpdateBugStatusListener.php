<?php

namespace App\Listeners;

use App\Events\UpdateBugStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateBugStatusListenerListener
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
     * @param  \App\Events\UpdateBugStatus  $event
     * @return void
     */
    public function handle(UpdateBugStatus $event)
    {
        //
    }
}
