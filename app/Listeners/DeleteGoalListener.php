<?php

namespace App\Listeners;

use App\Events\DeleteGoal;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteGoalListener
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
     * @param  \App\Events\DeleteGoal  $event
     * @return void
     */
    public function handle(DeleteGoal $event)
    {
        //
    }
}
