<?php

namespace App\Listeners;

use App\Events\PaymentReminderInvoice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PaymentReminderInvoiceListener
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
     * @param  \App\Events\PaymentReminderInvoice  $event
     * @return void
     */
    public function handle(PaymentReminderInvoice $event)
    {
        //
    }
}
