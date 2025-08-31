<?php

namespace App\Listeners;

use App\Events\CreateClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateClient
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
     * @param  \App\Events\CreateClient  $event
     * @return void
     */
    public function handle(CreateClient $event)
    {
        //
    }
}
