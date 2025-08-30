<?php

namespace App\Listeners;

use App\Events\UpdateChartOfAccount;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateChartOfAccountListenerListener
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
     * @param  \App\Events\UpdateChartOfAccount  $event
     * @return void
     */
    public function handle(UpdateChartOfAccount $event)
    {
        //
    }
}
