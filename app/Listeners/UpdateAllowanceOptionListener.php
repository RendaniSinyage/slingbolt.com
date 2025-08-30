<?php

namespace App\Listeners;

use App\Events\UpdateAllowanceOption;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateAllowanceOptionListenerListener
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
     * @param  \App\Events\UpdateAllowanceOption  $event
     * @return void
     */
    public function handle(UpdateAllowanceOption $event)
    {
        //
    }
}
