<?php

namespace App\Listeners;

use App\Events\EmployeeClockIn;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class EmployeeClockInListener
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
     * @param  \App\Events\EmployeeClockIn  $event
     * @return void
     */
    public function handle(EmployeeClockIn $event)
    {
        //
    }
}
