<?php

namespace App\Listeners;

use App\Events\UpdateCustomerCreditNote;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateCustomerCreditNoteListener
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
     * @param  \App\Events\UpdateCustomerCreditNote  $event
     * @return void
     */
    public function handle(UpdateCustomerCreditNote $event)
    {
        //
    }
}
