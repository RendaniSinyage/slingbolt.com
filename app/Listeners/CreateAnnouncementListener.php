<?php

namespace App\Listeners;

use App\Events\CreateAnnouncement;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateAnnouncementListener
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
     * @param  \App\Events\CreateAnnouncement  $event
     * @return void
     */
    public function handle(CreateAnnouncement $event)
    {
        //
    }
}
