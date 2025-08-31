<?php

namespace App\Listeners;

use App\Events\DeleteCustomField;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteCustomFieldListener
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
     * @param  \App\Events\DeleteCustomField  $event
     * @return void
     */
    public function handle(DeleteCustomField $event)
    {
        //
    }
}
