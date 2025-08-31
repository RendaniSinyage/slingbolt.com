<?php

namespace App\Listeners;

use App\Events\DeleteDesignation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteDesignationListener
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
     * @param  \App\Events\DeleteDesignation  $event
     * @return void
     */
    public function handle(DeleteDesignation $event)
    {
        //
    }
}
