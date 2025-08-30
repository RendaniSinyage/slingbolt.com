<?php

namespace App\Listeners;

use App\Events\CreateChartOfAccount;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateChartOfAccountListenerListener
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
     * @param  \App\Events\CreateChartOfAccount  $event
     * @return void
     */
    public function handle(CreateChartOfAccount $event)
    {
        //
    }
}
