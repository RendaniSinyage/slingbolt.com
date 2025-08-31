<?php

namespace App\Listeners;

use App\Events\UpdateDesignation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateDesignationListener
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
     * @param  \App\Events\UpdateDesignation  $event
     * @return void
     */
    public function handle(UpdateDesignation $event)
    {
        //
    }
}
