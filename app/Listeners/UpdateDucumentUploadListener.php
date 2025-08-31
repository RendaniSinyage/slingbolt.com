<?php

namespace App\Listeners;

use App\Events\UpdateDucumentUpload;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateDucumentUploadListener
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
     * @param  \App\Events\UpdateDucumentUpload  $event
     * @return void
     */
    public function handle(UpdateDucumentUpload $event)
    {
        //
    }
}
