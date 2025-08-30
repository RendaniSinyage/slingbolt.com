<?php

namespace App\Listeners;

use App\Events\CreateRole;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateRole
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
     * @param  \App\Events\CreateRole  $event
     * @return void
     */
    public function handle(CreateRole $event)
    {
        //
    }
}
