<?php

namespace App\Listeners;

use App\Events\CreateFormResponse;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateFormResponseListener
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
     * @param  \App\Events\CreateFormResponse  $event
     * @return void
     */
    public function handle(CreateFormResponse $event)
    {
        //
    }
}
