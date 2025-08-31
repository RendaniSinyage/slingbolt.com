<?php

namespace App\Listeners;

use App\Events\CreateGoal;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateGoalListener
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
     * @param  \App\Events\CreateGoal  $event
     * @return void
     */
    public function handle(CreateGoal $event)
    {
        //
    }
}
