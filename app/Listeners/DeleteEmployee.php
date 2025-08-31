<?php

namespace App\Listeners;

use App\Events\DeleteEmployee;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteEmployee
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
     * @param  \App\Events\DeleteEmployee  $event
     * @return void
     */
    public function handle(DeleteEmployee $event)
    {
        //
    }
}
