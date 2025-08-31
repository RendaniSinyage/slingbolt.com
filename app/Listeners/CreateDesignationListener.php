<?php

namespace App\Listeners;

use App\Events\CreateDesignation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateDesignationListener
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
     * @param  \App\Events\CreateDesignation  $event
     * @return void
     */
    public function handle(CreateDesignation $event)
    {
        //
    }
}
