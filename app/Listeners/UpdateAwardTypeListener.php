<?php

namespace App\Listeners;

use App\Events\UpdateAwardType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateAwardTypeListenerListener
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
     * @param  \App\Events\UpdateAwardType  $event
     * @return void
     */
    public function handle(UpdateAwardType $event)
    {
        //
    }
}
