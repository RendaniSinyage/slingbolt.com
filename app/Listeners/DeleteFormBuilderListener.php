<?php

namespace App\Listeners;

use App\Events\DeleteFormBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteFormBuilderListener
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
     * @param  \App\Events\DeleteFormBuilder  $event
     * @return void
     */
    public function handle(DeleteFormBuilder $event)
    {
        //
    }
}
