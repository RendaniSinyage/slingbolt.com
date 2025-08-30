<?php

namespace App\Listeners;

use App\Events\CreateAwardType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateAwardTypeListener
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
     * @param  \App\Events\CreateAwardType  $event
     * @return void
     */
    public function handle(CreateAwardType $event)
    {
        //
    }
}
