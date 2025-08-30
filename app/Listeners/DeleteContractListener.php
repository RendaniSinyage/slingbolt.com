<?php

namespace App\Listeners;

use App\Events\DeleteContract;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteContractListener
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
     * @param  \App\Events\DeleteContract  $event
     * @return void
     */
    public function handle(DeleteContract $event)
    {
        //
    }
}
