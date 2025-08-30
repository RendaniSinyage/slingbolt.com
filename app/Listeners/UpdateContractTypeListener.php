<?php

namespace App\Listeners;

use App\Events\UpdateContractType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateContractTypeListenerListener
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
     * @param  \App\Events\UpdateContractType  $event
     * @return void
     */
    public function handle(UpdateContractType $event)
    {
        //
    }
}
