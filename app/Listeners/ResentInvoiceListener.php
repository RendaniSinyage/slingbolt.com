<?php

namespace App\Listeners;

use App\Events\ResentInvoice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ResentInvoiceListener
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
     * @param  \App\Events\ResentInvoice  $event
     * @return void
     */
    public function handle(ResentInvoice $event)
    {
        //
    }
}
