<?php

namespace App\Listeners;

use App\Events\DeleteCustomer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteCustomer
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
     * @param  \App\Events\DeleteCustomer  $event
     * @return void
     */
    public function handle(DeleteCustomer $event)
    {
        //
    }
}
