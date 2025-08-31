<?php

namespace App\Listeners;

use App\Events\DeleteBugStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteBugStatusListener
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
     * @param  \App\Events\DeleteBugStatus  $event
     * @return void
     */
    public function handle(DeleteBugStatus $event)
    {
        //
    }
}
