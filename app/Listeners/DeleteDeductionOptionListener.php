<?php

namespace App\Listeners;

use App\Events\DeleteDeductionOption;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteDeductionOptionListener
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
     * @param  \App\Events\DeleteDeductionOption  $event
     * @return void
     */
    public function handle(DeleteDeductionOption $event)
    {
        //
    }
}
