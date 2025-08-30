<?php

namespace App\Listeners;

use App\Events\DeleteCompanyPolicy;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteCompanyPolicyListenerListener
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
     * @param  \App\Events\DeleteCompanyPolicy  $event
     * @return void
     */
    public function handle(DeleteCompanyPolicy $event)
    {
        //
    }
}
