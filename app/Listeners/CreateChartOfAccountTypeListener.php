<?php

namespace App\Listeners;

use App\Events\CreateChartOfAccountType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateChartOfAccountTypeListener
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
     * @param  \App\Events\CreateChartOfAccountType  $event
     * @return void
     */
    public function handle(CreateChartOfAccountType $event)
    {
        //
    }
}
