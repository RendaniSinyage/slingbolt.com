<?php

namespace App\Listeners;

use App\Events\UpdateDebitNote;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateDebitNoteListener
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
     * @param  \App\Events\UpdateDebitNote  $event
     * @return void
     */
    public function handle(UpdateDebitNote $event)
    {
        //
    }
}
