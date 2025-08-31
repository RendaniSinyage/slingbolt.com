<?php

namespace App\Listeners;

use App\Events\DeleteContractType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteContractTypeListener
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
     * @param  \App\Events\DeleteContractType  $event
     * @return void
     */
    public function handle(DeleteContractType $event)
    {
        //
    }
}
