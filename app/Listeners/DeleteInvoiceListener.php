<?php

namespace App\Listeners;

use App\Events\DeleteInvoice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteInvoice
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
     * @param  \App\Events\DeleteInvoice  $event
     * @return void
     */
    public function handle(DeleteInvoice $event)
    {
        //
    }
}
