<?php

namespace App\Listeners;

use App\Events\CreateEmployee;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateEmployee
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
     * @param  \App\Events\CreateEmployee  $event
     * @return void
     */
    public function handle(CreateEmployee $event)
    {
        //
    }
}
