<?php

namespace App\Listeners;

use App\Events\CreatePaymentInvoice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreatePaymentInvoiceListener
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
     * @param  \App\Events\CreatePaymentInvoice  $event
     * @return void
     */
    public function handle(CreatePaymentInvoice $event)
    {
        //
    }
}
