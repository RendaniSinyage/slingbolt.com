<?php

namespace App\Listeners;

use App\Events\DeleteWarehouse;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteWarehouseListener
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
     * @param  \App\Events\DeleteWarehouse  $event
     * @return void
     */
    public function handle(DeleteWarehouse $event)
    {
        //
    }
}
