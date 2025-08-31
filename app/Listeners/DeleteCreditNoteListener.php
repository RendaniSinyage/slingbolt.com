<?php

namespace App\Listeners;

use App\Events\DeleteCreditNote;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteCreditNoteListener
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
     * @param  \App\Events\DeleteCreditNote  $event
     * @return void
     */
    public function handle(DeleteCreditNote $event)
    {
        //
    }
}
