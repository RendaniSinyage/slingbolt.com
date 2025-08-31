<?php

namespace App\Listeners;

use App\Events\UpdateFormField;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateFormFieldListener
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
     * @param  \App\Events\UpdateFormField  $event
     * @return void
     */
    public function handle(UpdateFormField $event)
    {
        //
    }
}
