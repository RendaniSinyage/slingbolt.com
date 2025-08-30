<?php

namespace App\Listeners;

use App\Events\CreateDeal;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateDealListener
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
     * @param  \App\Events\CreateDeal  $event
     * @return void
     */
    public function handle(CreateDeal $event)
    {
        //
    }
}
