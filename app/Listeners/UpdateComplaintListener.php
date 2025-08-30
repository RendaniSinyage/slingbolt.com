<?php

namespace App\Listeners;

use App\Events\UpdateComplaint;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateComplaintListenerListener
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
     * @param  \App\Events\UpdateComplaint  $event
     * @return void
     */
    public function handle(UpdateComplaint $event)
    {
        //
    }
}
