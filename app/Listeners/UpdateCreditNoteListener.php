<?php

namespace App\Listeners;

use App\Events\UpdateCreditNote;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateCreditNoteListener
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
     * @param  \App\Events\UpdateCreditNote  $event
     * @return void
     */
    public function handle(UpdateCreditNote $event)
    {
        //
    }
}
