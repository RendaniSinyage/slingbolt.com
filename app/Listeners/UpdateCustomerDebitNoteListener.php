<?php

namespace App\Listeners;

use App\Events\UpdateCustomerDebitNote;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateCustomerDebitNoteListener
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
     * @param  \App\Events\UpdateCustomerDebitNote  $event
     * @return void
     */
    public function handle(UpdateCustomerDebitNote $event)
    {
        //
    }
}
