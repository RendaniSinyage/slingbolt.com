<?php

namespace App\Listeners;

use App\Events\UpdateChartOfAccountType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateChartOfAccountTypeListenerListener
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
     * @param  \App\Events\UpdateChartOfAccountType  $event
     * @return void
     */
    public function handle(UpdateChartOfAccountType $event)
    {
        //
    }
}
