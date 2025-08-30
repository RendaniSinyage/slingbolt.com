<?php

namespace App\Listeners;

use App\Events\UpdateInvoice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateInvoiceListener
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
