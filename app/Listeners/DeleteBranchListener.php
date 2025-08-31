<?php

namespace App\Listeners;

use App\Events\DeleteBranch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteBranchListener
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
     * @param  \App\Events\DeleteBranch  $event
     * @return void
     */
    public function handle(DeleteBranch $event)
    {
        //
    }
}
