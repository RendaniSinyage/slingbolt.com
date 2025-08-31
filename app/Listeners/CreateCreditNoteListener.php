<?php

namespace App\Listeners;

use App\Events\CreateCreditNote;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateCreditNoteListener
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
     * @param  \App\Events\CreateCreditNote  $event
     * @return void
     */
    public function handle(CreateCreditNote $event)
    {
        //
    }
}
