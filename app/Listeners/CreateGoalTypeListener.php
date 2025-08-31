<?php

namespace App\Listeners;

use App\Events\CreateGoalType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateGoalTypeListener
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
     * @param  \App\Events\CreateGoalType  $event
     * @return void
     */
    public function handle(CreateGoalType $event)
    {
        //
    }
}
