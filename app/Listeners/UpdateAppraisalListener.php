<?php

namespace App\Listeners;

use App\Events\UpdateAppraisal;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateAppraisalListener
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
     * @param  \App\Events\UpdateAppraisal  $event
     * @return void
     */
    public function handle(UpdateAppraisal $event)
    {
        //
    }
}
