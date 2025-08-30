<?php

namespace App\Listeners;

use App\Events\ConvertToInvoice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ConvertToInvoiceListener
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
     * @param  \App\Events\ConvertToInvoice  $event
     * @return void
     */
    public function handle(ConvertToInvoice $event)
    {
        //
    }
}
