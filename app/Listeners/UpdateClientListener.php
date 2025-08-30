<?php

namespace App\Listeners;

use App\Events\UpdateClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateClientListener
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
     * @param  \App\Events\UpdateClient  $event
     * @return void
     */
    public function handle(UpdateClient $event)
    {
        //
    }
}
