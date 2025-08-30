<?php

namespace App\Listeners;

use App\Events\DeleteAllowance;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteAllowanceListener
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
     * @param  \App\Events\DeleteAllowance  $event
     * @return void
     */
    public function handle(DeleteAllowance $event)
    {
        //
    }
}
