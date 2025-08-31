<?php

namespace App\Listeners;

use App\Events\UpdateGoal;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateGoalListener
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
     * @param  \App\Events\UpdateGoal  $event
     * @return void
     */
    public function handle(UpdateGoal $event)
    {
        //
    }
}
