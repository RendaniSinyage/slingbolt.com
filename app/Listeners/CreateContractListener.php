<?php

namespace App\Listeners;

use App\Events\CreateContract;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateContractListener
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
     * @param  \App\Events\CreateContract  $event
     * @return void
     */
    public function handle(CreateContract $event)
    {
        //
    }
}
