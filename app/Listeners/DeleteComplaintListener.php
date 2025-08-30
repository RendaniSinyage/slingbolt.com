<?php

namespace App\Listeners;

use App\Events\DeleteComplaint;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteComplaintListener
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
     * @param  \App\Events\DeleteComplaint  $event
     * @return void
     */
    public function handle(DeleteComplaint $event)
    {
        //
    }
}
