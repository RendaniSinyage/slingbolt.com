<?php

namespace App\Listeners;

use App\Events\DeleteCommission;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteCommissionListener
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
     * @param  \App\Events\DeleteCommission  $event
     * @return void
     */
    public function handle(DeleteCommission $event)
    {
        //
    }
}
