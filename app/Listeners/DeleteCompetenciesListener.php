<?php

namespace App\Listeners;

use App\Events\DeleteCompetencies;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteCompetenciesListenerListener
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
     * @param  \App\Events\DeleteCompetencies  $event
     * @return void
     */
    public function handle(DeleteCompetencies $event)
    {
        //
    }
}
