<?php

namespace App\Listeners;

use App\Events\DeleteRole;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteRoleListener
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
     * @param  \App\Events\DeleteRole  $event
     * @return void
     */
    public function handle(DeleteRole $event)
    {
        //
    }
}
