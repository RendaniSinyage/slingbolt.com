<?php

namespace App\Listeners;

use App\Events\CreateWarehouse;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateWarehouse
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
     * @param  \App\Events\CreateWarehouse  $event
     * @return void
     */
    public function handle(CreateWarehouse $event)
    {
        //
    }
}
