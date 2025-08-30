<?php

namespace App\Listeners;

use App\Events\CreateComplaint;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateComplaintListenerListener
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
     * @param  \App\Events\CreateComplaint  $event
     * @return void
     */
    public function handle(CreateComplaint $event)
    {
        //
    }
}
