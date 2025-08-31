<?php

namespace App\Listeners;

use App\Events\UpdateFormBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateFormBuilderListener
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
     * @param  \App\Events\UpdateFormBuilder  $event
     * @return void
     */
    public function handle(UpdateFormBuilder $event)
    {
        //
    }
}
