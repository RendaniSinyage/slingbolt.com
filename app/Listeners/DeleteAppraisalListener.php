<?php

namespace App\Listeners;

use App\Events\DeleteAppraisal;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteAppraisalListener
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
     * @param  \App\Events\DeleteAppraisal  $event
     * @return void
     */
    public function handle(DeleteAppraisal $event)
    {
        //
    }
}
