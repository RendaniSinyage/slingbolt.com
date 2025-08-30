<?php

namespace App\Listeners;

use App\Events\UpdateAward;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateAwardListenerListener
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
     * @param  \App\Events\UpdateAward  $event
     * @return void
     */
    public function handle(UpdateAward $event)
    {
        //
    }
}
