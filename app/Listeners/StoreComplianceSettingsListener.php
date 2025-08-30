<?php

namespace App\Listeners;

use App\Events\StoreComplianceSettings;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class StoreComplianceSettingsListenerListener
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
     * @param  \App\Events\StoreComplianceSettings  $event
     * @return void
     */
    public function handle(StoreComplianceSettings $event)
    {
        //
    }
}
