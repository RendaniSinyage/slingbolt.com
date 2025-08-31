<?php

namespace App\Listeners;

use App\Events\CreateFormBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateFormBuilderListener
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
     * @param  \App\Events\CreateFormBuilder  $event
     * @return void
     */
    public function handle(CreateFormBuilder $event)
    {
        //
    }
}
