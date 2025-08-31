<?php

namespace App\Listeners;

use App\Events\DeleteDepartment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteDepartmentListener
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
     * @param  \App\Events\DeleteDepartment  $event
     * @return void
     */
    public function handle(DeleteDepartment $event)
    {
        //
    }
}
