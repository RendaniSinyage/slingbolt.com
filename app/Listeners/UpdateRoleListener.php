<?php

namespace App\Listeners;

use App\Events\UpdateRole;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateRoleListener
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
     * @param  \App\Events\UpdateRole  $event
     * @return void
     */
    public function handle(UpdateRole $event)
    {
        //
    }
}
