<?php

namespace App\Listeners;

use App\Events\UpdateGoalType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateGoalTypeListener
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
     * @param  \App\Events\UpdateGoalType  $event
     * @return void
     */
    public function handle(UpdateGoalType $event)
    {
        //
    }
}
