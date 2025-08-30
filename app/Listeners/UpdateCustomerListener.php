<?php

namespace App\Listeners;

use App\Events\UpdateCustomer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateCustomer
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
     * @param  \App\Events\UpdateCustomer  $event
     * @return void
     */
    public function handle(UpdateCustomer $event)
    {
        //
    }
}
