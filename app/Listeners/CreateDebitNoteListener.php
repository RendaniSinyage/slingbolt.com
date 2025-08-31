<?php

namespace App\Listeners;

use App\Events\CreateDebitNote;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateDebitNoteListener
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
     * @param  \App\Events\CreateDebitNote  $event
     * @return void
     */
    public function handle(CreateDebitNote $event)
    {
        //
    }
}
