<?php

namespace App\Listeners;

use App\Events\CreateBudget;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateBudgetListenerListener
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
     * @param  \App\Events\CreateBudget  $event
     * @return void
     */
    public function handle(CreateBudget $event)
    {
        //
    }
}
