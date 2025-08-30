<?php

namespace App\Listeners;

use App\Events\CreateInvoice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateInvoiceListener
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
     * @param  \App\Events\CreateInvoice  $event
     * @return void
     */
    public function handle(CreateInvoice $event)
    {
        //
    }
}
