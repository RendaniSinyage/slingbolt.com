<?php

namespace App\Listeners;

use App\Events\UpdateWarehouse;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateWarehouseListener
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
     * @param  \App\Events\UpdateWarehouse  $event
     * @return void
     */
    public function handle(UpdateWarehouse $event)
    {
        //
    }
}
