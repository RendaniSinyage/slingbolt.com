<?php

namespace App\Listeners;

use App\Events\UpdateDeductionOption;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateDeductionOptionListener
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
     * @param  \App\Events\UpdateDeductionOption  $event
     * @return void
     */
    public function handle(UpdateDeductionOption $event)
    {
        //
    }
}
