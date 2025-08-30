<?php

namespace App\Listeners;

use App\Events\SentInvoice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SentInvoiceListener
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
     * @param  \App\Events\SentInvoice  $event
     * @return void
     */
    public function handle(SentInvoice $event)
    {
        //
    }
}
