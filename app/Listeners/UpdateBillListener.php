<?php

namespace App\Listeners;

use App\Events\UpdateBill;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateBill
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
     * @param  \App\Events\UpdateBill  $event
     * @return void
     */
    public function handle(UpdateBill $event)
    {
        //
    }
}
