<?php

namespace App\Listeners;

use App\Events\UpdateAnnouncement;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateAnnouncementListenerListener
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
     * @param  \App\Events\UpdateAnnouncement  $event
     * @return void
     */
    public function handle(UpdateAnnouncement $event)
    {
        //
    }
}
