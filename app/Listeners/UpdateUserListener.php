<?php

namespace App\Listeners;

use App\Events\UpdateUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateUserListener
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
     * @param  \App\Events\UpdateUser  $event
     * @return void
     */
    public function handle(UpdateUser $event)
    {
        //
    }
}
