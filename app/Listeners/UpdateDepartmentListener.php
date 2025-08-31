<?php

namespace App\Listeners;

use App\Events\UpdateDepartment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateDepartmentListener
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
     * @param  \App\Events\UpdateDepartment  $event
     * @return void
     */
    public function handle(UpdateDepartment $event)
    {
        //
    }
}
