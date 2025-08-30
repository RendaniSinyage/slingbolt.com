<?php

namespace App\Listeners;

use App\Events\UpdateCommission;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateCommissionListenerListener
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
     * @param  \App\Events\UpdateCommission  $event
     * @return void
     */
    public function handle(UpdateCommission $event)
    {
        //
    }
}
