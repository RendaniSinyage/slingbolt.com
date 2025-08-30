<?php

namespace App\Listeners;

use App\Events\CreateBugStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateBugStatusListenerListener
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
     * @param  \App\Events\CreateBugStatus  $event
     * @return void
     */
    public function handle(CreateBugStatus $event)
    {
        //
    }
}
