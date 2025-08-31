<?php

namespace App\Listeners;

use App\Events\UpdateEmployee;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateEmployeeListener
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
     * @param  \App\Events\UpdateEmployee  $event
     * @return void
     */
    public function handle(UpdateEmployee $event)
    {
        //
    }
}
