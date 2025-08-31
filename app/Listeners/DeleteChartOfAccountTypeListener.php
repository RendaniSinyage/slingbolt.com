<?php

namespace App\Listeners;

use App\Events\DeleteChartOfAccountType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteChartOfAccountTypeListener
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
     * @param  \App\Events\DeleteChartOfAccountType  $event
     * @return void
     */
    public function handle(DeleteChartOfAccountType $event)
    {
        //
    }
}
