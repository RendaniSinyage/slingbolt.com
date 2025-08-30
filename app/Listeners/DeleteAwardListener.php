<?php

namespace App\Listeners;

use App\Events\DeleteAward;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteAwardListenerListener
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
     * @param  \App\Events\DeleteAward  $event
     * @return void
     */
    public function handle(DeleteAward $event)
    {
        //
    }
}
