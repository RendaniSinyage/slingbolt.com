<?php

namespace App\Listeners;

use App\Events\UpdateInvoice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateInvoice
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
     * @param  \App\Events\UpdateInvoice  $event
     * @return void
     */
    public function handle(UpdateInvoice $event)
    {
        //
    }
}
