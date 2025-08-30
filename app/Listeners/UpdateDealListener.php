<?php

namespace App\Listeners;

use App\Events\UpdateDeal;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateDealListener
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
     * @param  \App\Events\UpdateDeal  $event
     * @return void
     */
    public function handle(UpdateDeal $event)
    {
        //
    }
}
