<?php

namespace App\Listeners;

use App\Events\DeleteBudget;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteBudgetListener
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
     * @param  \App\Events\DeleteBudget  $event
     * @return void
     */
    public function handle(DeleteBudget $event)
    {
        //
    }
}
