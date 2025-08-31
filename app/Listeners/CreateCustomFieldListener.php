<?php

namespace App\Listeners;

use App\Events\CreateCustomField;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateCustomFieldListener
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
     * @param  \App\Events\CreateCustomField  $event
     * @return void
     */
    public function handle(CreateCustomField $event)
    {
        //
    }
}
