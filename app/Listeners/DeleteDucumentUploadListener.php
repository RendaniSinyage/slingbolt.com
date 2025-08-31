<?php

namespace App\Listeners;

use App\Events\DeleteDucumentUpload;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteDucumentUploadListener
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
     * @param  \App\Events\DeleteDucumentUpload  $event
     * @return void
     */
    public function handle(DeleteDucumentUpload $event)
    {
        //
    }
}
