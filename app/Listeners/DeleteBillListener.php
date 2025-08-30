<?php

namespace App\Listeners;

use App\Events\DeleteBill;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteBillListener
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
     * @param  \App\Events\DeleteBill  $event
     * @return void
     */
    public function handle(DeleteBill $event)
    {
        //
    }
}
