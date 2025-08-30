<?php

namespace App\Listeners;

use App\Events\CreateAllowance;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateAllowanceListenerListener
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
     * @param  \App\Events\CreateAllowance  $event
     * @return void
     */
    public function handle(CreateAllowance $event)
    {
        //
    }
}
