<?php

namespace App\Listeners;

use App\Events\UpdateBranch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateBranchListenerListener
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
     * @param  \App\Events\UpdateBranch  $event
     * @return void
     */
    public function handle(UpdateBranch $event)
    {
        //
    }
}
