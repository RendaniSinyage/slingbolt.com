<?php

namespace App\Listeners;

use App\Events\CreateDucumentUpload;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateDucumentUploadListener
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
     * @param  \App\Events\CreateDucumentUpload  $event
     * @return void
     */
    public function handle(CreateDucumentUpload $event)
    {
        //
    }
}
