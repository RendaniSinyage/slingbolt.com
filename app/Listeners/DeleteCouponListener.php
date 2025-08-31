<?php

namespace App\Listeners;

use App\Events\DeleteCoupon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteCouponListener
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
     * @param  \App\Events\DeleteCoupon  $event
     * @return void
     */
    public function handle(DeleteCoupon $event)
    {
        //
    }
}
