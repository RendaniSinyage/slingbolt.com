<?php

namespace App\Listeners;

use App\Events\DeleteAsset;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteAssetListener
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
     * @param  \App\Events\DeleteAsset  $event
     * @return void
     */
    public function handle(DeleteAsset $event)
    {
        //
    }
}
