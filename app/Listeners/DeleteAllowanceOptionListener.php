<?php

namespace App\Listeners;

use App\Events\DeleteAllowanceOption;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteAllowanceOptionListener
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
     * @param  \App\Events\DeleteAllowanceOption  $event
     * @return void
     */
    public function handle(DeleteAllowanceOption $event)
    {
        //
    }
}
