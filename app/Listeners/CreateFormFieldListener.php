<?php

namespace App\Listeners;

use App\Events\CreateFormField;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateFormFieldListener
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
     * @param  \App\Events\CreateFormField  $event
     * @return void
     */
    public function handle(CreateFormField $event)
    {
        //
    }
}
