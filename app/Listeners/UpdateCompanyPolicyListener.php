<?php

namespace App\Listeners;

use App\Events\UpdateCompanyPolicy;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateCompanyPolicyListenerListener
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
     * @param  \App\Events\UpdateCompanyPolicy  $event
     * @return void
     */
    public function handle(UpdateCompanyPolicy $event)
    {
        //
    }
}
