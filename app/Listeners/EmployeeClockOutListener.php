<?php

namespace App\Listeners;

use App\Events\EmployeeClockOut;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class EmployeeClockOutListenerListener
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
     * @param  \App\Events\EmployeeClockOut  $event
     * @return void
     */
    public function handle(EmployeeClockOut $event)
    {
        //
    }
}
