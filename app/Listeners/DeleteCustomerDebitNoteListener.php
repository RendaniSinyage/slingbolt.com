<?php

namespace App\Listeners;

use App\Events\DeleteCustomerDebitNote;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteCustomerDebitNoteListener
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
     * @param  \App\Events\DeleteCustomerDebitNote  $event
     * @return void
     */
    public function handle(DeleteCustomerDebitNote $event)
    {
        //
    }
}
