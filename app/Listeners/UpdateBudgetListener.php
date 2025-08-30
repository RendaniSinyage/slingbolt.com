<?php

namespace App\Listeners;

use App\Events\UpdateBudget;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateBudgetListenerListener
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
     * @param  \App\Events\UpdateBudget  $event
     * @return void
     */
    public function handle(UpdateBudget $event)
    {
        //
    }
}
