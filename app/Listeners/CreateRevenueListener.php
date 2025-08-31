<?php

namespace App\Listeners;

use App\Events\CreateRevenue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateRevenueListener
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
     * @param  \App\Events\CreateRevenue  $event
     * @return void
     */
    public function handle(CreateRevenue $event)
    {
        //
    }
}
