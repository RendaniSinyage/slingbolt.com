<?php

namespace App\Listeners;

use App\Events\UpdateAsset;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateAssetListener
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
     * @param  \App\Events\UpdateAsset  $event
     * @return void
     */
    public function handle(UpdateAsset $event)
    {
        //
    }
}
