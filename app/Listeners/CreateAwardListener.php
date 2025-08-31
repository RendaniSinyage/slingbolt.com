<?php

namespace App\Listeners;

use App\Events\CreateAward;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateAwardListener
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
     * @param  \App\Events\CreateAward  $event
     * @return void
     */
    public function handle(CreateAward $event)
    {
        //
    }
}
