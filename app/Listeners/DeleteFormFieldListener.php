<?php

namespace App\Listeners;

use App\Events\DeleteFormField;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteFormFieldListener
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
     * @param  \App\Events\DeleteFormField  $event
     * @return void
     */
    public function handle(DeleteFormField $event)
    {
        //
    }
}
