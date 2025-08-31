<?php

namespace App\Listeners;

use App\Events\UpdateCustomField;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateCustomFieldListener
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
     * @param  \App\Events\UpdateCustomField  $event
     * @return void
     */
    public function handle(UpdateCustomField $event)
    {
        //
    }
}
