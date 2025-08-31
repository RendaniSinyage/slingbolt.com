<?php

namespace App\Listeners;

use App\Events\CreateCustomerDebitNote;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateCustomerDebitNoteListener
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
     * @param  \App\Events\CreateCustomerDebitNote  $event
     * @return void
     */
    public function handle(CreateCustomerDebitNote $event)
    {
        //
    }
}
