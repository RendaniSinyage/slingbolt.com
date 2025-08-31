<?php

namespace App\Listeners;

use App\Events\UpdateFormLeadConversion;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateFormLeadConversionListener
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
     * @param  \App\Events\UpdateFormLeadConversion  $event
     * @return void
     */
    public function handle(UpdateFormLeadConversion $event)
    {
        //
    }
}
