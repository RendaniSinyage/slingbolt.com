<?php

namespace App\Listeners;

use App\Events\CreateAllowanceOption;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateAllowanceOptionListenerListener
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
     * @param  \App\Events\CreateAllowanceOption  $event
     * @return void
     */
    public function handle(CreateAllowanceOption $event)
    {
        //
    }
}
