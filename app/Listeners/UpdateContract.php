<?php

namespace App\Listeners;

use App\Events\UpdateContract;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateContract
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
     * @param  \App\Events\UpdateContract  $event
     * @return void
     */
    public function handle(UpdateContract $event)
    {
        //
    }
}
