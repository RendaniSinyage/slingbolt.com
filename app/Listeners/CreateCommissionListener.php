<?php

namespace App\Listeners;

use App\Events\CreateCommission;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateCommissionListenerListener
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
     * @param  \App\Events\CreateCommission  $event
     * @return void
     */
    public function handle(CreateCommission $event)
    {
        //
    }
}
