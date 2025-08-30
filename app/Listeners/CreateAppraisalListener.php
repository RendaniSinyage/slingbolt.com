<?php

namespace App\Listeners;

use App\Events\CreateAppraisal;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateAppraisalListenerListener
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
     * @param  \App\Events\CreateAppraisal  $event
     * @return void
     */
    public function handle(CreateAppraisal $event)
    {
        //
    }
}
