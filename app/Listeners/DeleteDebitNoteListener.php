<?php

namespace App\Listeners;

use App\Events\DeleteDebitNote;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteDebitNoteListener
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
     * @param  \App\Events\DeleteDebitNote  $event
     * @return void
     */
    public function handle(DeleteDebitNote $event)
    {
        //
    }
}
