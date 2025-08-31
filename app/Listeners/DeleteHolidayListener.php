<?php

namespace App\Listeners;

use App\Events\DeleteHoliday;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteHolidayListener
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
     * @param  \App\Events\DeleteHoliday  $event
     * @return void
     */
    public function handle(DeleteHoliday $event)
    {
        //
    }
}
