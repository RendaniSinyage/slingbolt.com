<?php

namespace App\Listeners;

use App\Events\CreateHoliday;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateHolidayListener
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
     * @param  \App\Events\CreateHoliday  $event
     * @return void
     */
    public function handle(CreateHoliday $event)
    {
        //
    }
}
