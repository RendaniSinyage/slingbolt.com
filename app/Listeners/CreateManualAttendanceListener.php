<?php

namespace App\Listeners;

use App\Events\CreateManualAttendance;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateManualAttendanceListenerListener
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
     * @param  \App\Events\CreateManualAttendance  $event
     * @return void
     */
    public function handle(CreateManualAttendance $event)
    {
        //
    }
}
