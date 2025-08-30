<?php

namespace App\Listeners;

use App\Events\CreateBill;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateBill
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
     * @param  \App\Events\CreateBill  $event
     * @return void
     */
    public function handle(CreateBill $event)
    {
        //
    }
}
