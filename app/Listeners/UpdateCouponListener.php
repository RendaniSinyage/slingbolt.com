<?php

namespace App\Listeners;

use App\Events\UpdateCoupon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateCouponListener
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
     * @param  \App\Events\UpdateCoupon  $event
     * @return void
     */
    public function handle(UpdateCoupon $event)
    {
        //
    }
}
