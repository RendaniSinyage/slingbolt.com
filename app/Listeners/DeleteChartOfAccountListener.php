<?php

namespace App\Listeners;

use App\Events\DeleteChartOfAccount;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteChartOfAccountListenerListener
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
     * @param  \App\Events\DeleteChartOfAccount  $event
     * @return void
     */
    public function handle(DeleteChartOfAccount $event)
    {
        //
    }
}
