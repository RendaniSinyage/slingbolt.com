<?php

namespace App\Listeners;

use App\Events\UpdateBugStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateBugStatusListener
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
