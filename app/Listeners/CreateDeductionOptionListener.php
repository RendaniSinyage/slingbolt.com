<?php

namespace App\Listeners;

use App\Events\CreateDeductionOption;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateDeductionOptionListener
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
     * @param  \App\Events\CreateDeductionOption  $event
     * @return void
     */
    public function handle(CreateDeductionOption $event)
    {
        //
    }
}
