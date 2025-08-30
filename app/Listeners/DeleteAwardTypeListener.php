<?php

namespace App\Listeners;

use App\Events\DeleteAwardType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteAwardTypeListenerListener
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
     * @param  \App\Events\DeleteAwardType  $event
     * @return void
     */
    public function handle(DeleteAwardType $event)
    {
        //
    }
}
