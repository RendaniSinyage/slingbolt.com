<?php

namespace App\Listeners;

use App\Events\CreateCoupon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateCouponListener
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
     * @param  \App\Events\CreateCoupon  $event
     * @return void
     */
    public function handle(CreateCoupon $event)
    {
        //
    }
}
