<?php

namespace App\Listeners;

use App\Events\CreateContractType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateContractTypeListener
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
     * @param  \App\Events\CreateContractType  $event
     * @return void
     */
    public function handle(CreateContractType $event)
    {
        //
    }
}
